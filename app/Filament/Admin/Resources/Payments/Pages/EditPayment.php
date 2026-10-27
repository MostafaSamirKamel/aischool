<?php

namespace App\Filament\Admin\Resources\Payments\Pages;

use App\Filament\Admin\Resources\Payments\PaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;
    
    public function mount($record): void
    {
        parent::mount($record);
        
        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_payment.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();
                
            $this->redirect(PaymentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // تحميل بيانات الطالب والتسجيل عند التعديل
        if (isset($data['enrollment_id'])) {
            $enrollment = \App\Models\Enrollment::with([
                'student.user',
                'track',
                'enrollmentCourses.course',
                'enrollmentCourses.group.instructor.user',
                'installments'
            ])->find($data['enrollment_id']);

            if ($enrollment && $enrollment->student) {
                $data['student_id'] = $enrollment->student->id;
                $data['enrollment_code'] = $enrollment->enrollment_code;
                $data['track_name'] = $enrollment->track->name ?? 'غير محدد';
                $data['payment_plan'] = $enrollment->payment_plan;

                // حساب قيمة الكورسات
                $coursesTotalPrice = $enrollment->enrollmentCourses->sum('course_price');
                $data['courses_total'] = $coursesTotalPrice;
                $data['total_discount'] = $enrollment->discount ?? 0;

                // حساب إجمالي المدفوعات
                $paidUpfront = $enrollment->getCalculatedUpfrontAmount();
                $previousPayments = \App\Models\Payment::where('enrollment_id', $enrollment->id)
                    ->where('status', 'completed')
                    ->sum('amount');

                $totalPaid = 0;
                $totalPaidFromInstallments = 0;
                $otherPayments = floatval($previousPayments);

                if ($enrollment->payment_plan === 'installments') {
                    $totalPaidFromInstallments = floatval($enrollment->installments()->sum('paid_amount'));
                    $otherPayments = max($totalPaidFromInstallments, floatval($previousPayments));
                    $totalPaid = $paidUpfront + $otherPayments;
                } else {
                    $totalPaid = $enrollment->getEffectivePaidAmount();
                    $otherPayments = max(0, $totalPaid - $paidUpfront);
                }

                $data['paid_upfront'] = $paidUpfront;
                $data['other_payments'] = $otherPayments;
                $data['total_paid'] = $totalPaid;
                $data['remaining_balance'] = ($coursesTotalPrice - $enrollment->discount - $totalPaid);

                // بيانات الكورسات
                $coursesInfo = [];
                foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
                    if (!$enrollmentCourse->course) continue;
                    
                    // حساب المحاضرات من الجلسات المجدولة
                    $totalSessions = \App\Models\SessionSchedule::where('group_id', $enrollmentCourse->group_id)
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->count();
                        
                    $completedSessions = \App\Models\AttendanceSession::where('group_id', $enrollmentCourse->group_id)
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->count();
                        
                    $remainingSessions = max(0, $totalSessions - $completedSessions);

                    $coursesInfo[] = [
                        'course_name' => $enrollmentCourse->course->name,
                        'course_code' => $enrollmentCourse->course->course_code ?? '',
                        'course_price' => $enrollmentCourse->course_price,
                        'group_name' => $enrollmentCourse->group->name ?? 'غير محدد',
                        'instructor_name' => $enrollmentCourse->group->instructor->user->name ?? 'غير محدد',
                        'total_sessions' => $totalSessions,
                        'completed_sessions' => $completedSessions,
                        'remaining_sessions' => $remainingSessions,
                    ];
                }
                $data['courses_info'] = $coursesInfo;

                // بيانات الأقساط إذا كان نظام التقسيط - استخدام نفس التنسيق في PaymentForm
                if ($enrollment->payment_plan === 'installments' && $enrollment->installments->isNotEmpty()) {
                    $installmentsInfo = [];
                    foreach ($enrollment->installments as $installment) {
                        $remaining = floatval($installment->amount) - floatval($installment->paid_amount);
                        $installmentsInfo[] = [
                            'id' => $installment->id,
                            'installment_number' => $installment->installment_number,
                            'amount' => floatval($installment->amount),
                            'due_date' => $installment->due_date ? $installment->due_date->format('Y-m-d') : '',
                            'paid_amount' => floatval($installment->paid_amount),
                            'paid_date' => $installment->paid_date?->format('Y-m-d') ?? '-',
                            'status' => match ($installment->status) {
                                'pending' => 'قيد الانتظار',
                                'paid' => 'مدفوع',
                                'overdue' => 'متأخر',
                                'partial' => 'مدفوع جزئي',
                                default => $installment->status
                            },
                            'remaining' => $remaining,
                        ];
                    }
                    $data['installments_info'] = $installmentsInfo;
                } else {
                    $data['installments_info'] = [];
                }
            }
        } else {
            // إذا لم يكن هناك enrollment_id، تأكد من وجود قيم افتراضية
            $data['installments_info'] = [];
            $data['courses_info'] = [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['vault_id'])) {
            $vault = \App\Models\Vault::find($data['vault_id']);
            if ($vault) {
                $data['payment_method'] = match ($vault->type) {
                    'cash' => 'cash',
                    'vodafone', 'instapay', 'mobile_wallet', 'wallet' => 'mobile_wallet',
                    'bank' => 'bank_transfer',
                    default => 'cash',
                };
            }
        }

        unset($data['student_id']);
        unset($data['barcode']);
        unset($data['track_name']);
        unset($data['courses_total']);
        unset($data['enrollment_code']);
        unset($data['paid_upfront']);
        unset($data['total_discount']);
        unset($data['other_payments']);
        unset($data['total_paid']);
        unset($data['remaining_balance']);
        unset($data['payment_plan']);
        unset($data['payment_preview_info']);
        unset($data['installments_info']);
        unset($data['payment_history_info']);
        unset($data['courses_info']);

        return $data;
    }

    protected function afterSave(): void
    {
        // PaymentObserver handles installment reallocation and enrollment financial sync.
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
            return [];
        }
        
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('filament/admin/edit_payment.title');
    }

    public function getHeading(): string
    {
        return __('filament/admin/edit_payment.title');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_payment.title');
    }

}
