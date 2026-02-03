<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Payment::class)
                ->select('*')
                ->with(['student.user', 'course'])
                ->allowedFilters([
                    AllowedFilter::partial('transaction_id'),
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::exact('payment_method'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['transaction_id', 'student.user.full_name', 'course.course_name'])),
                ])
                ->allowedSorts(['payment_date', 'amount', 'status'])
                ->latest('payment_date')
                ->paginate($this->limit())
                ->withQueryString();

            $stats = [
                'totalRevenue' => Payment::where('status', 'completed')->sum('amount'),
                'pendingPayments' => Payment::where('status', 'pending')->sum('amount'),
                'refundedAmount' => Payment::where('status', 'refunded')->sum('amount'),
            ];

            DB::commit();

            return Inertia::render('Admin/Payments/Index', [
                'items' => $items,
                'stats' => $stats,
                'statusOptions' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'failed', 'label' => 'Failed'],
                    ['value' => 'refunded', 'label' => 'Refunded'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function show(Payment $payment): Response
    {
        DB::beginTransaction();

        try {
            $payment->load(['student.user', 'course']);

            DB::commit();

            return Inertia::render('Admin/Payments/Show', [
                'item' => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,failed,refunded'],
        ]);

        DB::beginTransaction();

        try {
            $payment->update($validated);

            $enrollment = Enrollment::where('student_id', $payment->student_id)
                ->where('course_id', $payment->course_id)
                ->first();

            if ($enrollment) {
                $enrollment->update(['payment_status' => $validated['status']]);
            }

            DB::commit();

            return back()->withSuccess(__('Payment status updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function refund(Payment $payment)
    {
        if ($payment->status !== 'completed') {
            return back()->withError(__('Only completed payments can be refunded.'));
        }

        DB::beginTransaction();

        try {
            $payment->update(['status' => 'refunded']);

            $enrollment = Enrollment::where('student_id', $payment->student_id)
                ->where('course_id', $payment->course_id)
                ->first();

            if ($enrollment) {
                $enrollment->update(['payment_status' => 'refunded']);
            }

            DB::commit();

            return back()->withSuccess(__('Payment refunded successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
