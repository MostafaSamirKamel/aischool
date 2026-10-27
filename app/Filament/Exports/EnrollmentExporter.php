<?php

namespace App\Filament\Exports;

use App\Models\Enrollment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EnrollmentExporter extends Exporter
{
    protected static ?string $model = Enrollment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('enrollment_code')
                ->label('كود التسجيل'),
            ExportColumn::make('student.student_code')
                ->label('كود الطالب'),
            ExportColumn::make('student.user.name')
                ->label('اسم الطالب'),
            ExportColumn::make('group.name')
                ->label('المجموعة'),
            ExportColumn::make('group.course.name')
                ->label('الكورس'),
            ExportColumn::make('enrollment_date')
                ->label('تاريخ التسجيل'),
            ExportColumn::make('total_amount')
                ->label('المبلغ الإجمالي'),
            ExportColumn::make('discount')
                ->label('الخصم'),
            ExportColumn::make('paid_amount')
                ->label('المبلغ المدفوع'),
            ExportColumn::make('payment_plan')
                ->label('خطة الدفع'),
            ExportColumn::make('installments_count')
                ->label('عدد الأقساط'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('start_date')
                ->label('تاريخ البدء'),
            ExportColumn::make('end_date')
                ->label('تاريخ الانتهاء'),
            ExportColumn::make('tenant.name')
                ->label('الفرع'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your enrollment export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
