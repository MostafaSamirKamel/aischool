<?php

namespace App\Filament\Student\Pages;

use Filament\Tables;
use Filament\Pages\Page;
use App\Models\Enrollment;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class StudentEnrollments extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.student.pages.student-enrollments';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('تسجيلاتي');
    }

    public function getTitle(): string
    {
        return __('تسجيلاتي');
    }

    public function getHeading(): string
    {
        return __('تسجيلاتي والكورسات المختارة');
    }

    public function table(Table $table): Table
    {
        $student = auth()->user()->student;

        return $table
            ->query(
                Enrollment::query()
                    ->where('student_id', $student?->id)
                    ->with(['enrollmentCourses.course', 'enrollmentCourses.group', 'group', 'track'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('enrollment_code')
                    ->label(__('رقم التسجيل'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('المجموعة الأساسية'))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label(__('المبلغ الإجمالي'))
                    ->money('EGP')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label(__('المبلغ المدفوع'))
                    ->getStateUsing(fn($record) => $record->getEffectivePaidAmount())
                    ->money('EGP')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label(__('المتبقي'))
                    ->getStateUsing(fn($record) => $record->getRemainingBalance())
                    ->money('EGP')
                    ->alignment('center')
                    ->color(fn($record) => $record->getRemainingBalance() > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label(__('تاريخ التسجيل'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('الحالة'))
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'active' => 'نشط',
                        'completed' => 'مكتمل',
                        'suspended' => 'معلق',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'active',
                        'info' => 'completed',
                        'warning' => 'suspended',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'active' => 'نشط',
                        'completed' => 'مكتمل',
                        'suspended' => 'معلق',
                        'cancelled' => 'ملغي',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
