<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Payment;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PaymentsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function getTableHeading(): ?string
    {
        return 'المدفوعات الأخيرة';
    }

    public function table(Table $table): Table
    {
        $studentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
        
        // Get enrollment IDs for this student
        $enrollmentIds = $studentId ? \App\Models\Enrollment::where('student_id', $studentId)->pluck('id')->toArray() : [];
        
        return $table
            ->query(
                Payment::query()
                    ->when(!empty($enrollmentIds), fn($query) => $query->whereIn('enrollment_id', $enrollmentIds), fn($query) => $query->whereRaw('1 = 0'))
                    ->with(['enrollment.track'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('enrollment.track.name')
                    ->label('المسار')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'cash' => 'نقدي',
                        'card' => 'بطاقة',
                        'bank_transfer' => 'تحويل بنكي',
                        'online' => 'أونلاين',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'completed' => 'مكتمل',
                        'pending' => 'معلق',
                        'failed' => 'فشل',
                        default => $state,
                    }),
            ]);
    }
}
