<?php

namespace App\Filament\Admin\Resources\Enrollments\Actions;

use App\Models\Enrollment;
use App\Models\Vault;
use App\Services\Enrollment\EnrollmentCancellationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class CancelSubscriptionAction
{
    public static function make(): Action
    {
        return Action::make('cancel_subscription')
            ->label('إلغاء الاشتراك')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn(Enrollment $record): bool => $record->status !== 'cancelled' && auth()->user()->can('cancel', $record))
            ->modalHeading('إلغاء تسجيل الطالب')
            ->modalDescription('إلغاء اشتراك الطالب وإسقاط المديونية المتبقية تلقائياً، مع إمكانية استرداد جزء من المدفوع.')
            ->modalWidth(Width::TwoExtraLarge)
            ->modalSubmitActionLabel('حفظ الإلغاء')
            ->modalCancelActionLabel('رجوع')
            ->closeModalByClickingAway(false)
            ->fillForm(fn(): array => [
                'cancellation_reason' => '',
                'refund_amount' => null,
                'refund_vault_id' => null,
            ])
            ->form(function (Enrollment $record): array {
                $studentName = $record->student?->user?->name ?? 'غير محدد';
                $coursesNames = $record->courses->pluck('name')->implode(', ');
                if (empty($coursesNames) && $record->track) {
                    $coursesNames = 'مسار: ' . $record->track->name;
                }
                $groupName = $record->groups->first()?->name ?? $record->group?->name ?? 'غير محددة';

                $totalAmount = (float) $record->total_amount;
                $paidAmount = $record->getEffectivePaidAmount();
                $remainingDebt = $record->getRemainingBalance();
                $alreadyRefunded = (float) ($record->refunded_amount ?? 0);
                $maxRefundable = max(0, round($paidAmount - $alreadyRefunded, 2));

                $canRefund = auth()->user()->can('executeRefund', $record);

                return [
                    // SECTION 1: Student Information & Financial Snapshot (Read-only)
                    Section::make('1. بيانات الطالب والاشتراك')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Placeholder::make('student_summary')
                                ->label('')
                                ->content(new HtmlString("
                                    <div class='bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-sm'>
                                        <div class='grid grid-cols-1 md:grid-cols-3 gap-3 mb-3'>
                                            <div>
                                                <span class='block text-xs text-slate-500 dark:text-slate-400'>اسم الطالب</span>
                                                <strong class='text-slate-900 dark:text-white font-bold text-base'>{$studentName}</strong>
                                            </div>
                                            <div>
                                                <span class='block text-xs text-slate-500 dark:text-slate-400'>الكورس / المسار</span>
                                                <strong class='text-slate-800 dark:text-slate-200'>{$coursesNames}</strong>
                                            </div>
                                            <div>
                                                <span class='block text-xs text-slate-500 dark:text-slate-400'>المجموعة</span>
                                                <strong class='text-slate-800 dark:text-slate-200'>{$groupName}</strong>
                                            </div>
                                        </div>
                                        <div class='grid grid-cols-3 gap-3 pt-3 border-t border-slate-200 dark:border-slate-800 text-center'>
                                            <div class='p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-100 dark:border-slate-700'>
                                                <span class='block text-xs text-slate-500 dark:text-slate-400'>إجمالي سعر الاشتراك</span>
                                                <span class='font-bold text-slate-900 dark:text-white'>" . number_format($totalAmount, 2) . " ج.م</span>
                                            </div>
                                            <div class='p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-100 dark:border-slate-700'>
                                                <span class='block text-xs text-slate-500 dark:text-slate-400'>إجمالي المسدد حتى الآن</span>
                                                <span class='font-bold text-emerald-600 dark:text-emerald-400'>" . number_format($paidAmount, 2) . " ج.م</span>
                                            </div>
                                            <div class='p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-100 dark:border-slate-700'>
                                                <span class='block text-xs text-slate-500 dark:text-slate-400'>المديونية المتبقية القائمة</span>
                                                <span class='font-bold text-red-600 dark:text-red-400'>" . number_format($remainingDebt, 2) . " ج.م</span>
                                            </div>
                                        </div>
                                        <div class='mt-3 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 p-2 rounded border border-amber-200 dark:border-amber-800 flex items-center gap-1.5'>
                                            <span>ℹ️</span>
                                            <span>وفقاً لسياسة المدرسة، سيتم إسقاط أي مديونية متبقية (" . number_format($remainingDebt, 2) . " ج.م) تلقائياً عند حفظ الإلغاء، مع الاحتفاظ بكامل سجل المدفوعات التاريخية.</span>
                                        </div>
                                    </div>
                                ")),
                        ]),

                    // SECTION 2: Cancellation Reason (Required)
                    Section::make('2. سبب إلغاء التسجيل')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->schema([
                            Textarea::make('cancellation_reason')
                                ->label('سبب إلغاء التسجيل')
                                ->placeholder('اكتب سبب إلغاء التسجيل بالتفصيل (مثل: ظروف خاصة، تغيير المواعيد، انتقال الطالب...)...')
                                ->required()
                                ->rows(3)
                                ->maxLength(500)
                                ->helperText('حقل إلزامي لتوثيق سبب الإلغاء في سجلات الطالب والمؤسسة.'),
                        ]),

                    // SECTION 3: Optional Refund Amount
                    Section::make('3. استرداد مبلغ للطالب (اختياري)')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            TextInput::make('refund_amount')
                                ->label('المبلغ المسترد للطالب — اختياري')
                                ->placeholder('0.00')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue($maxRefundable)
                                ->live()
                                ->helperText(function () use ($maxRefundable, $canRefund) {
                                    if (!$canRefund) {
                                        return new HtmlString('<span class="text-amber-600 font-medium">⚠️ ليس لديك صلاحية صرف استرداد مالي من الخزنة. يمكنك إلغاء التسجيل فقط بدون استرداد.</span>');
                                    }
                                    return "الحد الأقصى القابل للاسترداد: " . number_format($maxRefundable, 2) . " ج.م (اتركه فارغاً أو 0 إذا لم يكن هناك استرداد).";
                                }),

                            // SECTION 4: Vault Selection (Shown ONLY when refund_amount > 0)
                            Select::make('refund_vault_id')
                                ->label('الخزنة المصروف منها')
                                ->visible(fn($get) => (float) $get('refund_amount') > 0)
                                ->options(function () {
                                    $tenantId = auth()->user()?->tenant_id;
                                    $query = Vault::where('is_active', true);
                                    if (!auth()->user()?->is_super_admin && $tenantId) {
                                        $query->where('tenant_id', $tenantId);
                                    }
                                    return $query->get()->mapWithKeys(function ($v) {
                                        return [$v->id => $v->getDisplayNameWithBalance()];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->required(fn($get) => (float) $get('refund_amount') > 0)
                                ->rules([
                                    fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $amount = (float) $get('refund_amount');
                                        if ($value && $amount > 0) {
                                            $vault = Vault::find($value);
                                            if ($vault && (float) $vault->getCurrentBalance() < $amount) {
                                                $fail('رصيد الخزنة المحددة غير كافٍ لصرف مبلغ الاسترداد.');
                                            }
                                        }
                                    },
                                ])
                                ->helperText('اختر الخزنة النشطة التي سيتم صرف مبلغ الاسترداد منها فوراً.'),
                        ]),
                ];
            })
            ->action(function (Enrollment $record, array $data): void {
                $service = app(EnrollmentCancellationService::class);

                try {
                    $cancellation = $service->cancelSimplified($record, auth()->user(), [
                        'reason' => $data['cancellation_reason'] ?? '',
                        'refund_amount' => $data['refund_amount'] ?? null,
                        'vault_id' => $data['refund_vault_id'] ?? null,
                        'payment_method' => 'cash',
                    ]);

                    $hasRefund = ((float) $cancellation->refunded_amount > 0);
                    $successMessage = $hasRefund
                        ? 'تم إلغاء التسجيل واسترداد المبلغ من الخزنة بنجاح.'
                        : 'تم إلغاء تسجيل الطالب بنجاح.';

                    Notification::make()
                        ->title('نجاح العملية')
                        ->body($successMessage)
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('بيانات غير صحيحة')
                        ->body(collect($e->errors())->flatten()->first() ?? 'يرجى مراجعة البيانات المدخلة.')
                        ->danger()
                        ->send();
                } catch (AuthorizationException $e) {
                    Notification::make()
                        ->title('غير مصرح')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('فشل عملية إلغاء التسجيل')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
