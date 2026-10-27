<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\Installment;

class MyPayments extends Page
{
    protected string $view = 'filament.student.pages.my-payments';
    protected static ?string $title = 'مدفوعاتي واشتراكاتي';
    protected static ?string $navigationLabel = 'مدفوعاتي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 8;

    public function getTitle(): string
    {
        return __('مدفوعاتي واشتراكاتي');
    }

    public function getHeading(): string
    {
        return __('مدفوعاتي واشتراكاتي');
    }

    protected function getViewData(): array
    {
        $student = auth()->user()->student;
        
        $enrollments = Enrollment::where('student_id', $student?->id)
            ->with(['enrollmentCourses.course', 'installments'])
            ->get();
            
        $enrollmentIds = $enrollments->pluck('id');

        $payments = Payment::query()
            ->whereIn('enrollment_id', $enrollmentIds)
            ->with(['enrollment.enrollmentCourses.course'])
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function($payment) {
                $courses = $payment->enrollment?->enrollmentCourses->pluck('course.name')->unique()->join(', ') ?? 'دفع عام';
                return [
                    'id' => $payment->payment_code ?? $payment->id,
                    'course' => $courses,
                    'date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '-',
                    'method' => $payment->payment_method ?? 'نقدي',
                    'amount' => $payment->amount,
                    'status' => 'completed'
                ];
            });

        $installments = Installment::query()
            ->whereIn('enrollment_id', $enrollmentIds)
            ->with(['enrollment.enrollmentCourses.course'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function($inst) {
                $courses = $inst->enrollment?->enrollmentCourses->pluck('course.name')->unique()->join(', ') ?? 'قسط عام';
                $status = $inst->is_paid ? 'completed' : (($inst->due_date && $inst->due_date->isPast()) ? 'overdue' : 'upcoming');
                return [
                    'number' => $inst->id, // or order if exists
                    'course' => $courses,
                    'due' => $inst->due_date ? $inst->due_date->format('Y-m-d') : '-',
                    'amount' => $inst->amount,
                    'status' => $status,
                    'ago_days' => ($inst->due_date && $inst->due_date->isPast()) ? $inst->due_date->diffInDays(now()) : 0
                ];
            });

        $totalGross = $enrollments->sum('total_amount');
        $totalDiscount = $enrollments->sum('discount');
        $totalPaid = $enrollments->sum('paid_amount');
        $totalRemaining = $enrollments->sum('remaining_amount');

        return [
            'student' => $student,
            'payments' => $payments,
            'installments' => $installments,
            'totals' => [
                'gross' => $totalGross,
                'discount' => $totalDiscount,
                'paid' => $totalPaid,
                'remaining' => $totalRemaining,
                'count' => $payments->count()
            ]
        ];
    }
}
