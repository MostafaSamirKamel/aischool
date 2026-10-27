<?php

namespace App\Filament\Exports;

use App\Models\Course;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class CourseExporter extends Exporter
{
    protected static ?string $model = Course::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('اسم الكورس'),
            ExportColumn::make('track.name')
                ->label('المسار'),
            ExportColumn::make('description')
                ->label('الوصف'),
            ExportColumn::make('price')
                ->label('السعر'),
            ExportColumn::make('duration_months')
                ->label('المدة (شهور)'),
            ExportColumn::make('sessions_count')
                ->label('عدد الحصص'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('tenant.name')
                ->label('الفرع'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your course export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
