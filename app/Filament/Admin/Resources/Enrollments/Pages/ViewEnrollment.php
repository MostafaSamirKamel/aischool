<?php

namespace App\Filament\Admin\Resources\Enrollments\Pages;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Admin\Resources\Enrollments\EnrollmentResource;

class ViewEnrollment extends ViewRecord
{
    protected static string $resource = EnrollmentResource::class;

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات التسجيل')
                    ->schema([
                        TextEntry::make('enrollment_code')
                            ->label('رقم التسجيل')
                            ->copyable()
                            ->icon('heroicon-o-hashtag'),

                        TextEntry::make('student.user.name')
                            ->label('الطالب'),

                        TextEntry::make('group.name')
                            ->label('المجموعة الأساسية'),
                    ])
                    ->columns(3),

                Section::make('المعلومات المالية')
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->money('EGP'),

                        TextEntry::make('paid_amount')
                            ->label('المدفوع')
                            ->money('EGP'),

                        TextEntry::make('discount')
                            ->label('الخصم')
                            ->money('EGP'),

                        TextEntry::make('remaining_balance')
                            ->label('المتبقي')
                            ->money('EGP')
                            ->getStateUsing(fn($record) => $record->getRemainingBalance()),
                    ])
                    ->columns(4),

                Section::make('التواريخ')
                    ->schema([
                        TextEntry::make('enrollment_date')
                            ->label('تاريخ التسجيل')
                            ->date('d/m/Y'),

                        TextEntry::make('start_date')
                            ->label('تاريخ البدء')
                            ->date('d/m/Y'),

                        TextEntry::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->date('d/m/Y'),
                    ])
                    ->columns(3),

                Section::make('الاشتراك ملغي — تفاصيل الإلغاء والتسوية المالية')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn($record) => $record && $record->status === 'cancelled')
                    ->schema([
                        TextEntry::make('cancelled_at')
                            ->label('تاريخ وتوقيت الإلغاء')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('cancelledBy.name')
                            ->label('الموظف المسؤول عن الإلغاء')
                            ->placeholder('غير محدد'),

                        TextEntry::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->weight('bold')
                            ->color('danger'),

                        TextEntry::make('settlement_type_label')
                            ->label('طريقة التسوية المالية')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('settlement_status_label')
                            ->label('حالة التسوية')
                            ->badge()
                            ->color(fn($record) => $record->settlement_status === 'completed' ? 'success' : 'warning'),

                        TextEntry::make('waived_amount')
                            ->label('المبلغ المعفى (الساقط)')
                            ->money('EGP')
                            ->color('warning'),

                        TextEntry::make('retained_amount')
                            ->label('المحتجز للمدرسة')
                            ->money('EGP'),

                        TextEntry::make('refundable_amount')
                            ->label('مبلغ الاسترداد المقترح')
                            ->money('EGP')
                            ->color('primary'),

                        TextEntry::make('refunded_amount')
                            ->label('المبلغ المصروف فعلياً')
                            ->money('EGP')
                            ->color('success'),

                        TextEntry::make('refund_status_label')
                            ->label('حالة صرف الاسترداد')
                            ->badge()
                            ->color(fn($record) => match($record->refund_status) {
                                'completed' => 'success',
                                'pending' => 'warning',
                                'partially_refunded' => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make('refundVault.name')
                            ->label('خزنة الصرف')
                            ->placeholder('-'),

                        TextEntry::make('cancellation_notes')
                            ->label('ملاحظات التسوية الإضافية')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \App\Filament\Admin\Resources\Enrollments\Actions\CancelSubscriptionAction::make(),
            \App\Filament\Admin\Resources\Enrollments\Actions\ExecuteRefundAction::make(),
            EditAction::make()
                ->visible(fn($record) => $record->status !== 'cancelled'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Admin\Resources\Enrollments\Widgets\EnrollmentCoursesWidget::class,
            \App\Filament\Admin\Resources\Enrollments\Widgets\EnrollmentInstallmentsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'تفاصيل التسجيل #' . $this->record->enrollment_code;
    }

    public function getInstallments()
    {
        $installments = $this->record->installments()
            ->orderBy('installment_number', 'asc')
            ->get();

        foreach ($installments as $installment) {
            $installment->distributed_paid = (float) $installment->paid_amount;
            $installment->distributed_status = $installment->status;
        }

        return $installments;
    }
}
