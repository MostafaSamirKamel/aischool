<?php

namespace App\Filament\Exports;

use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PaymentExporter extends Exporter
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('payment_code')
                ->label('كود الدفعة'),
            ExportColumn::make('enrollment.enrollment_code')
                ->label('كود التسجيل'),
            ExportColumn::make('enrollment.student.student_code')
                ->label('كود الطالب'),
            ExportColumn::make('enrollment.student.user.name')
                ->label('اسم الطالب'),
            ExportColumn::make('amount')
                ->label('المبلغ'),
            ExportColumn::make('vault.name')
                ->label('الخزنة المستلمة'),
            ExportColumn::make('payment_method')
                ->label('طريقة الدفع (تاريخي)'),
            ExportColumn::make('payment_date')
                ->label('تاريخ الدفع'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('transaction_reference')
                ->label('مرجع المعاملة'),
            ExportColumn::make('receivedBy.name')
                ->label('استلمه'),
            ExportColumn::make('tenant.name')
                ->label('الفرع'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payment export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
