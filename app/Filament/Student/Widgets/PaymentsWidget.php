<?php

namespace App\Filament\Student\Widgets;

use App\Models\Payment;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PaymentsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 5;

    public function getTableHeading(): ?string
    {
        return 'المدفوعات الأخيرة';
    }

    public function table(Table $table): Table
    {
        $student = Student::where('user_id', auth()->id())->first();

        // Get enrollment IDs for this student
        // السطر 29 يفضل زي ما هو
        $enrollmentIds = $student ? \App\Models\Enrollment::where('student_id', $student->id)->pluck('id')->toArray() : [];

        return $table
            ->query(
                Payment::query()
                    // السطر 34: استخدمنا !empty بدلاً من isNotEmpty
                    ->when(!empty($enrollmentIds), fn($query) => $query->whereIn('enrollment_id', $enrollmentIds))
                    ->with(['enrollment.enrollmentCourses.course'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('enrollment.enrollmentCourses.course.name')
                    ->label('الكورس')
                    ->formatStateUsing(function ($record) {
                        return $record->enrollment?->enrollmentCourses->pluck('course.name')->unique()->join(', ') ?? 'غير محدد';
                    })
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'completed' => 'مكتمل',
                        'pending' => 'معلق',
                        'failed' => 'فشل',
                        default => $state,
                    }),
            ]);
    }
}
