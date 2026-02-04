<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $payments = QueryBuilder::for(Payment::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('payment_method'),
            ])
            ->allowedSorts(['payment_date', 'amount'])
            ->where('student_id', $student->id)
            ->with('course')
            ->latest('payment_date')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'payment_date' => $payment->payment_date,
                    'status' => $payment->status,
                    'course' => [
                        'id' => $payment->course->id,
                        'course_name' => $payment->course->course_name,
                        'course_code' => $payment->course->course_code,
                        'thumbnail' => $payment->course->thumbnail_url,
                    ],
                ];
            }),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'total_pages' => $payments->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'payment_method' => 'required|string|in:credit_card,debit_card,paypal,bank_transfer',
            'amount' => 'required|numeric|min:0',
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

        if ((float) $validated['amount'] !== (float) $course->price) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount does not match course price',
                'errors' => [
                    'amount' => "Expected amount: {$course->price}",
                ],
            ], 422);
        }

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

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                'payment_date' => now(),
                'status' => 'completed', // In real app, this would be 'pending' until payment processor confirms
            ]);

            $enrollment->update(['payment_status' => 'paid']);

            activity()
                ->causedBy($user)
                ->performedOn($payment)
                ->withProperties([
                    'course_id' => $course->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ])
                ->log('Payment processed');

            // Notify student that payment was successful
            Notification::create([
                'user_id' => $user->id,
                'title' => __('Payment Successful'),
                'message' => __('Your payment for :course has been completed. You can now access the course.', [
                    'course' => $course->course_name,
                ]),
                'type' => 'success',
                'related_id' => $enrollment->id,
                'is_read' => false,
            ]);

            // Notify admins about the payment
            $admins = User::where('user_type', 'admin')
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => __('New Payment Received'),
                    'message' => __(':student paid :amount for :course', [
                        'student' => $user->full_name,
                        'amount' => '$' . number_format($payment->amount, 2),
                        'course' => $course->course_name,
                    ]),
                    'type' => 'success',
                    'related_id' => $payment->id,
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful. You can now access the course.',
                'can_access_course' => true,
                'data' => [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date,
                    'status' => $payment->status,
                    'enrollment_id' => $enrollment->id,
                    'course' => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $payment->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $payment->load('course');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'payment_date' => $payment->payment_date,
                'status' => $payment->status,
                'course' => [
                    'id' => $payment->course->id,
                    'course_name' => $payment->course->course_name,
                    'course_code' => $payment->course->course_code,
                    'price' => $payment->course->price,
                    'thumbnail' => $payment->course->thumbnail_url,
                ],
            ],
        ]);
    }
}
