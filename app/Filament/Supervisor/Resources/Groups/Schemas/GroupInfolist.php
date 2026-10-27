<?php

namespace App\Filament\Supervisor\Resources\Groups\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المجموعة')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('اسم المجموعة'),
                                TextEntry::make('code')
                                    ->label('الكود'),
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'active' => 'success',
                                        'cancelled' => 'danger',
                                        'completed' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'قيد الانتظار',
                                        'active' => 'نشطة',
                                        'cancelled' => 'ملغاة',
                                        'completed' => 'مكتملة',
                                        default => $state,
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('course.name')
                                    ->label('الدورة'),
                                TextEntry::make('instructor.user.name')
                                    ->label('المدرس'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('تاريخ البداية')
                                    ->date('Y-m-d'),
                                TextEntry::make('end_date')
                                    ->label('تاريخ النهاية')
                                    ->date('Y-m-d'),
                                TextEntry::make('students_count')
                                    ->label('عدد الطلاب')
                                    ->state(fn ($record) => $record->students()->count()),
                            ]),
                    ]),

                Section::make('معلومات إضافية')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('max_students')
                                    ->label('الحد الأقصى للطلاب'),
                                TextEntry::make('schedule')
                                    ->label('الجدول الزمني'),
                            ]),
                        TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('الطلاب المسجلين')
                    ->schema([
                        RepeatableEntry::make('students')
                            ->label('')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('اسم الطالب'),
                                TextEntry::make('student_id')
                                    ->label('رقم الطالب'),
                                TextEntry::make('user.email')
                                    ->label('البريد الإلكتروني'),
                                TextEntry::make('user.phone')
                                    ->label('رقم الهاتف'),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),
            ]);
    }
}
