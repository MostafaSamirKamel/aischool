<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('student_code')
                ->label('كود الطالب'),
            ExportColumn::make('user.name')
                ->label('الاسم'),
            ExportColumn::make('user.email')
                ->label('البريد الإلكتروني'),
            ExportColumn::make('national_id')
                ->label('الرقم القومي'),
            ExportColumn::make('birth_date')
                ->label('تاريخ الميلاد'),
            ExportColumn::make('gender')
                ->label('النوع'),
            ExportColumn::make('phone')
                ->label('الهاتف'),
            ExportColumn::make('phone_2')
                ->label('هاتف 2'),
            ExportColumn::make('address')
                ->label('العنوان'),
            ExportColumn::make('city')
                ->label('المدينة'),
            ExportColumn::make('education_level')
                ->label('المستوى التعليمي'),
            ExportColumn::make('school_name')
                ->label('المدرسة'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('tenant.name')
                ->label('الفرع'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
