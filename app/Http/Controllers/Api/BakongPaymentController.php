<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Services\BakongService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BakongPaymentController extends Controller
{
    protected BakongService $bakongService;

    public function __construct(BakongService $bakongService)
    {
        $this->bakongService = $bakongService;
    }

    /**
     * Generate Bakong KHQR for course payment
     */
    public function generateQR(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $course = Course::find($validated['course_id']);

        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Course is not available',
            ], 422);
        }

        if ($course->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This course is free, no payment required',
            ], 422);
        }

        // Check enrollment
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Please enroll in the course first',
            ], 422);
        }

        if ($enrollment->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Course already paid',
            ], 422);
        }

        // Check for existing pending payment
        $existingPayment = Payment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('payment_method', 'bakong')
            ->where('status', 'pending')
            ->where('qr_expires_at', '>', now())
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => true,
                'message' => 'Existing QR code found',
                'data' => [
                    'payment_id' => $existingPayment->id,
                    'qr_string' => $existingPayment->qr_string,
                    'transaction_id' => $existingPayment->bakong_transaction_id,
                    'amount' => $existingPayment->amount,
                    'currency' => config('bakong.currency'),
                    'expires_at' => $existingPayment->qr_expires_at,
                    'course' => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                    ],
                ],
            ]);
        }

        DB::beginTransaction();

        try {
            // Generate KHQR
            $qrResult = $this->bakongService->generateKHQR([
                'amount' => $course->price,
                'currency' => config('bakong.currency'),
                'bill_number' => 'EDU-' . $course->id . '-' . $student->id,
                'purpose' => 'Payment for ' . $course->course_name,
            ]);

            if (!$qrResult['success']) {
                throw new \Exception($qrResult['message'] ?? 'Failed to generate QR code');
            }

            $qrData = $qrResult['data'];

            // Create pending payment record
            $payment = Payment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'bakong',
                'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                'bakong_transaction_id' => $qrData['transaction_id'],
                'qr_string' => $qrData['qr_string'],
                'md5_hash' => $qrData['md5'],
                'qr_expires_at' => $qrData['expires_at'],
                'bakong_status' => 'PENDING',
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($payment)
                ->withProperties([
                    'course_id' => $course->id,
                    'amount' => $payment->amount,
                    'bakong_transaction_id' => $payment->bakong_transaction_id,
                ])
                ->log('Bakong QR generated');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'qr_string' => $payment->qr_string,
                    'transaction_id' => $payment->bakong_transaction_id,
                    'amount' => $payment->amount,
                    'currency' => config('bakong.currency'),
                    'expires_at' => $payment->qr_expires_at,
                    'course' => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bakong QR generation failed', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'course_id' => $course->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $payment->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if ($payment->payment_method !== 'bakong') {
            return response()->json([
                'success' => false,
                'message' => 'This is not a Bakong payment',
            ], 422);
        }

        // If already completed, return success
        if ($payment->status === 'completed') {
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => 'SUCCESS',
                    'payment_status' => $payment->status,
                    'message' => 'Payment completed',
                    'can_access_course' => true,
                ],
            ]);
        }

        // Check if QR expired
        if ($payment->isQRExpired()) {
            $payment->update([
                'bakong_status' => 'EXPIRED',
                'status' => 'failed',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => 'EXPIRED',
                    'payment_status' => 'failed',
                    'message' => 'QR code has expired. Please generate a new one.',
                    'can_access_course' => false,
                ],
            ]);
        }

        // Check with Bakong API
        $statusResult = $this->bakongService->checkPaymentStatus($payment->md5_hash);

        if ($statusResult['success'] && isset($statusResult['data']['status'])) {
            $bakongStatus = $statusResult['data']['status'];

            if ($bakongStatus === 'SUCCESS') {
                // Payment successful - update records
                $this->processSuccessfulPayment($payment, $statusResult['data']);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment_id' => $payment->id,
                        'status' => 'SUCCESS',
                        'payment_status' => 'completed',
                        'message' => 'Payment successful! You can now access the course.',
                        'can_access_course' => true,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $bakongStatus,
                    'payment_status' => $payment->status,
                    'message' => $this->getStatusMessage($bakongStatus),
                    'can_access_course' => false,
                    'expires_at' => $payment->qr_expires_at,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => 'PENDING',
                'payment_status' => $payment->status,
                'message' => 'Waiting for payment',
                'can_access_course' => false,
                'expires_at' => $payment->qr_expires_at,
            ],
        ]);
    }

    /**
     * Handle Bakong webhook callback
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Bakong-Signature');

        // Verify webhook signature
        if (!$this->bakongService->verifyWebhookSignature($payload, $signature ?? '')) {
            Log::warning('Bakong webhook signature verification failed', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        $data = $request->all();

        Log::info('Bakong webhook received', $data);

        // Find payment by MD5 hash or transaction ID
        $payment = Payment::where('md5_hash', $data['md5'] ?? null)
            ->orWhere('bakong_transaction_id', $data['transaction_id'] ?? null)
            ->first();

        if (!$payment) {
            Log::warning('Bakong webhook: Payment not found', $data);
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already processed',
            ]);
        }

        $status = $data['status'] ?? 'UNKNOWN';

        if ($status === 'SUCCESS') {
            $this->processSuccessfulPayment($payment, $data);
        } elseif ($status === 'FAILED') {
            $payment->update([
                'bakong_status' => 'FAILED',
                'bakong_response' => $data,
                'status' => 'failed',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed',
        ]);
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(Payment $payment, array $responseData): void
    {
        DB::beginTransaction();

        try {
            // Update payment
            $payment->update([
                'bakong_status' => 'SUCCESS',
                'bakong_response' => $responseData,
                'status' => 'completed',
            ]);

            // Update enrollment
            $enrollment = Enrollment::where('student_id', $payment->student_id)
                ->where('course_id', $payment->course_id)
                ->first();

            if ($enrollment) {
                $enrollment->update(['payment_status' => 'paid']);
            }

            // Get student user
            $student = $payment->student;
            $user = $student->user;
            $course = $payment->course;

            // Notify student
            Notification::create([
                'user_id' => $user->id,
                'title' => __('Payment Successful'),
                'message' => __('Your Bakong payment for :course has been completed. You can now access the course.', [
                    'course' => $course->course_name,
                ]),
                'type' => 'success',
                'related_id' => $enrollment->id ?? $payment->id,
                'is_read' => false,
            ]);

            // Notify admins
            $admins = User::where('user_type', 'admin')
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => __('Bakong Payment Received'),
                    'message' => __(':student paid $:amount for :course via Bakong', [
                        'student' => $user->full_name,
                        'amount' => number_format($payment->amount, 2),
                        'course' => $course->course_name,
                    ]),
                    'type' => 'success',
                    'related_id' => $payment->id,
                    'is_read' => false,
                ]);
            }

            activity()
                ->performedOn($payment)
                ->withProperties([
                    'course_id' => $payment->course_id,
                    'amount' => $payment->amount,
                    'bakong_transaction_id' => $payment->bakong_transaction_id,
                ])
                ->log('Bakong payment completed');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process successful payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get human-readable status message
     */
    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            'PENDING' => 'Waiting for payment. Please scan the QR code with Bakong app.',
            'SUCCESS' => 'Payment successful!',
            'FAILED' => 'Payment failed. Please try again.',
            'EXPIRED' => 'QR code has expired. Please generate a new one.',
            default => 'Unknown status',
        };
    }

    /**
     * Simulate payment success (for testing only)
     */
    public function simulateSuccess(Request $request, Payment $payment): JsonResponse
    {
        if (!$this->bakongService->isTestMode()) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation only available in test mode',
            ], 403);
        }

        $user = $request->user();
        $student = $user->student;

        if (!$student || $payment->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already completed',
            ], 422);
        }

        $this->processSuccessfulPayment($payment, [
            'simulated' => true,
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment simulated successfully',
            'can_access_course' => true,
        ]);
    }
}
