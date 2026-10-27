<?php

namespace App\Filament\Admin\Resources\Enrollments\Pages;

use App\Filament\Admin\Resources\Enrollments\EnrollmentResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected array $selectedCourses = [];
    protected array $installmentsData = [];

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_enrollment.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(EnrollmentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store courses selection temporarily
        $this->selectedCourses = $data['courses'] ?? [];
        unset($data['courses']);

        // Store installments data temporarily
        $this->installmentsData = $data['installments'] ?? [];
        unset($data['installments']);

        // Add tenant_id
        $data['tenant_id'] = auth()->user()->tenant_id;

        // If no upfront payment was made, do not assign a vault (no money moved)
        if (floatval($data['paid_amount'] ?? 0) <= 0) {
            $data['vault_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $coursesWithPrices = [];
        if (!empty($this->selectedCourses)) {
            foreach ($this->selectedCourses as $courseId) {
                $course = \App\Models\Course::find($courseId);
                if ($course) {
                    $coursesWithPrices[$courseId] = [
                        'course_price' => $course->price,
                        'group_id' => null, // سيتم تحديدها في شاشة التعديل
                        'status' => 'inactive', // غير نشط حتى يتم تحديد المجموعة
                    ];
                }
            }
        }

        $installmentsData = $this->installmentsData;
        $tenantId = auth()->user()->tenant_id;
        $paidUpfront = floatval($record->paid_amount ?? 0);

        \DB::afterCommit(function () use ($record, $coursesWithPrices, $installmentsData, $tenantId, $paidUpfront) {
            $record->refresh();

            if (!empty($coursesWithPrices)) {
                $record->courses()->sync($coursesWithPrices);
            }

            // استخدام الخدمة المتخصصة لإنشاء الأقساط
            // ⚠️ ملاحظة: الأقساط محسوبة بعد خصم المقدم
            // المقدم لا يُطبق على الأقساط - يبقى منفصل
            // فقط المدفوعات من شاشة Payments تغير حالة الأقساط
            if (!empty($installmentsData)) {
                $installmentService = new \App\Services\Enrollment\InstallmentCalculationService();

                // إنشاء الأقساط (كلها pending)
                $installmentService->createInstallmentsForEnrollment($record, $installmentsData);

                // ملاحظة: لا نستدعي applyAdvancePaymentToInstallments
                // المقدم محسوب في enrollment->paid_amount فقط
            }
        });
    }

    public function getTitle(): string
    {
        return __('filament/admin/create_enrollment.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_enrollment.title');
    }

}
