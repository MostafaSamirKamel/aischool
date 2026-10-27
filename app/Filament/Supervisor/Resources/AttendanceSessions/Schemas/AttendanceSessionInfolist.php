<?php

namespace App\Filament\Supervisor\Resources\AttendanceSessions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الجلسة')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('attendance_date')
                                    ->label('تاريخ الجلسة')
                                    ->date('Y-m-d'),
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'قيد الانتظار',
                                        'completed' => 'مكتملة',
                                        'cancelled' => 'ملغاة',
                                        default => $state,
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('group.name')
                                    ->label('المجموعة'),
                                TextEntry::make('instructor.user.name')
                                    ->label('المدرس')
                                    ->default('—'),
                            ]),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ]),

                Section::make('إحصائيات الحضور')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('attendances_present')
                                    ->label('الحاضرون')
                                    ->state(fn ($record) => $record->attendances()->where('status', 'present')->count())
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('attendances_absent')
                                    ->label('الغائبون')
                                    ->state(fn ($record) => $record->attendances()->where('status', 'absent')->count())
                                    ->badge()
                                    ->color('danger'),
                                TextEntry::make('attendances_late')
                                    ->label('المتأخرون')
                                    ->state(fn ($record) => $record->attendances()->where('status', 'late')->count())
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ]),

                Section::make('سجل الحضور')
                    ->schema([
                        RepeatableEntry::make('attendances')
                            ->label('')
                            ->schema([
                                TextEntry::make('student.user.name')
                                    ->label('اسم الطالب'),
                                TextEntry::make('student.student_id')
                                    ->label('رقم الطالب'),
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'present' => 'success',
                                        'absent' => 'danger',
                                        'late' => 'warning',
                                        'excused' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'present' => 'حاضر',
                                        'absent' => 'غائب',
                                        'late' => 'متأخر',
                                        'excused' => 'معذور',
                                        default => $state,
                                    }),
                                TextEntry::make('notes')
                                    ->label('ملاحظات'),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),
            ]);
    }
}
