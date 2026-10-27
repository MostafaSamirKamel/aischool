<?php

namespace App\Filament\Exports;

use App\Models\Group;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class GroupExporter extends Exporter
{
    protected static ?string $model = Group::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('اسم المجموعة'),
            ExportColumn::make('course.name')
                ->label('الكورس'),
            ExportColumn::make('course.track.name')
                ->label('المسار'),
            ExportColumn::make('classroom.name')
                ->label('القاعة'),
            ExportColumn::make('instructor.name')
                ->label('المحاضر'),
            ExportColumn::make('supervisor.name')
                ->label('المشرف'),
            ExportColumn::make('capacity')
                ->label('العدد الحالي'),
            ExportColumn::make('max_students')
                ->label('الحد الأقصى'),
            ExportColumn::make('start_date')
                ->label('تاريخ البدء'),
            ExportColumn::make('end_date')
                ->label('تاريخ الانتهاء'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('tenant.name')
                ->label('الفرع'),
        ];
    }
    public static function getName(): string
    {
        return 'تصدير المجموعات';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your group export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}