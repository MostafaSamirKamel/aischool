<?php

namespace App\Filament\Admin\Resources\Enrollments\Actions;

use App\Models\Enrollment;
use App\Models\Vault;
use App\Services\Enrollment\EnrollmentCancellationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

class ExecuteRefundAction
{
    public static function make(): Action
    {
        return Action::make('execute_refund')
            ->label('صرف الاسترداد')
            ->icon('heroicon-o-currency-dollar')
            ->color('success')
            ->visible(function (Enrollment $record): bool {
                $hasPendingRefund = $record->status === 'cancelled'
                    && $record->refundable_amount > 0
                    && ($record->refundable_amount - $record->refunded_amount) > 0;

                return $hasPendingRefund && auth()->user()->can('executeRefund', $record);
            })
            ->modalHeading('صرف الاسترداد المالي للاشتراك الملغي')
            ->modalDescription('صرف المستحقات المستردة للطالب من الخزنة المعتمدة وتحديث السجلات المحاسبية.')
            ->modalWidth(Width::TwoExtraLarge)
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('تأكيد صرف الاسترداد')
            ->fillForm(function (Enrollment $record): array {
                $pendingAmount = max(0, round((float) $record->refundable_amount - (float) $record->refunded_amount, 2));

                return [
                    'amount' => $pendingAmount,
                ];
            })
            ->form(function (Enrollment $record): array {
                $pendingAmount = max(0, round((float) $record->refundable_amount - (float) $record->refunded_amount, 2));
                $studentName = $record->student?->user?->name ?? 'طالب';

                return [
                    Placeholder::make('summary_header')
                        ->label('بيانات الاسترداد')
                        ->content(new HtmlString("
                            <div class='bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 text-sm'>
                                <p><strong>الطالب:</strong> {$studentName}</p>
                                <p><strong>كود التسجيل:</strong> {$record->enrollment_code}</p>
                                <p><strong>إجمالي المستحق للاسترداد:</strong> " . number_format($record->refundable_amount, 2) . " ج.م</p>
                                <p><strong>المصروف سابقاً:</strong> " . number_format($record->refunded_amount, 2) . " ج.م</p>
                                <p class='text-emerald-700 dark:text-emerald-300 font-bold mt-1'>المتبقي القابل للصرف: " . number_format($pendingAmount, 2) . " ج.م</p>
                            </div>
                        ")),

                    TextInput::make('amount')
                        ->label('المبلغ المراد صرفه الآن (ج.م)')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue($pendingAmount)
                        ->default($pendingAmount),

                    Select::make('vault_id')
                        ->label('الخزنة المصروف منها')
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
                        ->required()
                        ->rules([
                            fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $amount = (float) $get('amount');
                                if ($value && $amount > 0) {
                                    $vault = Vault::find($value);
                                    if ($vault && (float) $vault->getCurrentBalance() < $amount) {
                                        $fail('رصيد الخزنة المحددة غير كافٍ لإتمام عملية الصرف.');
                                    }
                                }
                            },
                        ]),

                    Textarea::make('notes')
                        ->label('ملاحظات الصرف (اختياري)')
                        ->placeholder('أدخل أي ملاحظات على إيصال الصرف...')
                        ->rows(2),
                ];
            })
            ->action(function (Enrollment $record, array $data): void {
                $service = app(EnrollmentCancellationService::class);

                try {
                    $result = $service->executeRefund(
                        $record,
                        auth()->user(),
                        (float) $data['amount'],
                        (int) $data['vault_id'],
                        'cash',
                        $data['notes'] ?? null
                    );

                    Notification::make()
                        ->title('تم صرف الاسترداد المالي بنجاح')
                        ->body("تم تسجيل سند الصرف رقم {$result['expense_code']} وخصم المبلغ من الخزنة.")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('فشل صرف الاسترداد')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
