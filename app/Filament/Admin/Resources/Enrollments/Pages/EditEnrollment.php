<?php

namespace App\Filament\Admin\Resources\Enrollments\Pages;

use App\Filament\Admin\Resources\Enrollments\EnrollmentResource;
use App\Services\Enrollment\StudentGroupTransferService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected array $selectedCourses = [];
    protected array $installmentsData = [];
    protected ?int $targetGroupId = null;
    protected ?int $initialGroupId = null;
    protected bool $confirmTransferProgress = false;
    protected ?string $transferReason = null;
    protected bool $groupTransferPerformed = false;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin && is_null($this->record->tenant_id)) {
            Notification::make()
                ->title(__('filament/admin/edit_enrollment.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات غير مرتبطة بفرع')
                ->danger()
                ->send();

            $this->redirect(EnrollmentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing courses for editing
        $this->record->load('enrollmentCourses.course', 'installments', 'group');

        $data['courses'] = $this->record->enrollmentCourses
            ->pluck('course.id')
            ->filter()
            ->toArray();

        // Populate current group
        $data['group_id'] = $this->record->group_id 
            ?? $this->record->enrollmentCourses->first()?->group_id
            ?? $this->record->groups->first()?->id;

        // Load existing installments
        $data['installments'] = $this->record->installments
            ->map(function ($installment) {
                return [
                    'id' => $installment->id,
                    'installment_number' => $installment->installment_number,
                    'amount' => $installment->amount,
                    'due_date' => $installment->due_date,
                    'status' => $installment->status,
                    'paid_amount' => $installment->paid_amount,
                    'paid_date' => $installment->paid_date,
                    'notes' => $installment->notes,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->initialGroupId = $this->record->group_id 
            ?? $this->record->enrollmentCourses->first()?->group_id
            ?? $this->record->groups->first()?->id;

        $this->targetGroupId = !empty($data['group_id']) ? (int) $data['group_id'] : null;
        $this->confirmTransferProgress = !empty($data['confirm_transfer_progress']);
        $this->transferReason = $data['transfer_reason'] ?? null;

        unset(
            $data['confirm_transfer_progress'],
            $data['transfer_reason'],
            $data['has_progress_difference'],
            $data['progress_difference_message']
        );

        // Store courses selection temporarily
        $this->selectedCourses = $data['courses'] ?? [];
        unset($data['courses']);

        // Store installments data temporarily
        $this->installmentsData = $data['installments'] ?? [];
        unset($data['installments']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // If a group change is proposed, validate through StudentGroupTransferService
        if ($this->targetGroupId && (int) $this->targetGroupId !== (int) $this->initialGroupId) {
            $targetGroup = \App\Models\Group::find($this->targetGroupId);
            if (!$targetGroup) {
                Notification::make()
                    ->title('خطأ')
                    ->body('المجموعة المحددة غير موجودة')
                    ->danger()
                    ->persistent()
                    ->send();
                $this->halt();
            }

            $transferService = app(StudentGroupTransferService::class);

            try {
                $progressInfo = $transferService->validateTransfer($this->record, $targetGroup);

                if ($progressInfo['has_difference'] && !$this->confirmTransferProgress) {
                    Notification::make()
                        ->title('تنبيه فرق التقدم الأكاديمي')
                        ->body($progressInfo['warning_message'])
                        ->warning()
                        ->persistent()
                        ->send();
                    $this->halt();
                }
            } catch (ValidationException $e) {
                $firstError = collect($e->errors())->flatten()->first();
                Notification::make()
                    ->title('تعذر نقل الطالب')
                    ->body($firstError)
                    ->danger()
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }
    }

    protected function afterSave(): void
    {
        // Execute student group transfer if group has changed
        if ($this->targetGroupId && (int) $this->targetGroupId !== (int) $this->initialGroupId) {
            $targetGroup = \App\Models\Group::find($this->targetGroupId);
            if ($targetGroup) {
                $transferService = app(StudentGroupTransferService::class);
                $transferService->transfer($this->record, $targetGroup, [
                    'transferred_by' => auth()->user(),
                    'reason' => $this->transferReason,
                    'progress_warning_acknowledged' => $this->confirmTransferProgress,
                ]);
                $this->groupTransferPerformed = true;
            }
        }

        // Sync courses with their prices to pivot table
        if (!empty($this->selectedCourses)) {
            $coursesWithPrices = [];

            // Get existing enrollment courses to preserve group_id
            $existingEnrollmentCourses = $this->record->enrollmentCourses()
                ->get()
                ->keyBy('course_id');

            foreach ($this->selectedCourses as $courseId) {
                $course = \App\Models\Course::find($courseId);
                if ($course) {
                    $existingEC = $existingEnrollmentCourses->get($courseId);
                    $assignedGroupId = ($this->targetGroupId && $courseId == $this->record->group?->course_id)
                        ? $this->targetGroupId
                        : ($existingEC ? $existingEC->group_id : null);

                    $coursesWithPrices[$courseId] = [
                        'course_price' => $course->price,
                        'group_id' => $assignedGroupId,
                        'status' => $assignedGroupId ? 'active' : ($existingEC ? $existingEC->status : 'inactive'),
                    ];
                }
            }

            $this->record->courses()->sync($coursesWithPrices);
        }

        // استخدام الخدمة المتخصصة لتحديث الأقساط
        if (!empty($this->installmentsData)) {
            $tenantId = auth()->user()->tenant_id ?? $this->record->tenant_id;
            $installmentService = new \App\Services\Enrollment\InstallmentCalculationService();

            // حفظ معرفات الأقساط المحفوظة
            $existingIds = [];
            
            foreach ($this->installmentsData as $installmentData) {
                if (isset($installmentData['id']) && !empty($installmentData['id'])) {
                    // تحديث القسط الموجود
                    $installment = \App\Models\Installment::find($installmentData['id']);
                    if ($installment && $installment->enrollment_id === $this->record->id) {
                        $installment->update($installmentData);
                        $existingIds[] = $installment->id;
                    }
                } else {
                    // إنشاء قسط جديد
                    $installmentData['enrollment_id'] = $this->record->id;
                    $installmentData['tenant_id'] = $tenantId;
                    unset($installmentData['id']);
                    
                    $newInstallment = \App\Models\Installment::create($installmentData);
                    $existingIds[] = $newInstallment->id;
                }
            }

            // حذف الأقساط التي تمت إزالتها من النموذج
            $this->record->installments()
                ->whereNotIn('id', $existingIds)
                ->delete();
        } else {
            // إذا لم يكن هناك بيانات أقساط، احذفها جميعاً
            $this->record->installments()->delete();
        }

        // تحديث tenant_id لأي أقساط تفتقدها
        if ($this->record->installments()->exists()) {
            $this->record->installments()->whereNull('tenant_id')->update([
                'tenant_id' => auth()->user()->tenant_id ?? $this->record->tenant_id
            ]);
        }

        // Send appropriate success notification
        if ($this->groupTransferPerformed) {
            Notification::make()
                ->title('تم نقل الطالب بنجاح')
                ->body('تم نقل الطالب إلى المجموعة الجديدة بنجاح.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('تم التحديث بنجاح')
                ->body('تم تحديث بيانات التسجيل رقم: ' . $this->record->enrollment_code)
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin && is_null($this->record->tenant_id)) {
            return [];
        }

        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Admin\Resources\Enrollments\Widgets\EnrollmentCoursesWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return __('filament/admin/edit_enrollment.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_enrollment.title');
    }
}
