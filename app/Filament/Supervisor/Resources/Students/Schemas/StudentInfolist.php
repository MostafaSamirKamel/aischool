<?php

namespace App\Filament\Supervisor\Resources\Students\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الطالب الأساسية')
                    ->schema([
                        ImageEntry::make('user.profile_photo')
                            ->label('الصورة الشخصية')
                            ->circular(),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('الاسم'),
                                TextEntry::make('student_id')
                                    ->label('رقم الطالب'),
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'inactive' => 'warning',
                                        'suspended' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'active' => 'نشط',
                                        'inactive' => 'غير نشط',
                                        'suspended' => 'موقوف',
                                        default => $state,
                                    }),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('user.email')
                                    ->label('البريد الإلكتروني'),
                                TextEntry::make('user.phone')
                                    ->label('رقم الهاتف'),
                                TextEntry::make('enrollment_date')
                                    ->label('تاريخ التسجيل')
                                    ->date('Y-m-d'),
                            ]),
                    ]),

                Section::make('المجموعات المسجل فيها')
                    ->schema([
                        RepeatableEntry::make('groups')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('اسم المجموعة'),
                                TextEntry::make('course.name')
                                    ->label('الدورة'),
                                TextEntry::make('status')
                                    ->label('حالة المجموعة')
                                    ->badge(),
                                TextEntry::make('start_date')
                                    ->label('تاريخ البداية')
                                    ->date('Y-m-d'),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),

                Section::make('معلومات إضافية')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('emergency_contact')
                                    ->label('جهة الاتصال للطوارئ'),
                                TextEntry::make('emergency_phone')
                                    ->label('هاتف الطوارئ'),
                            ]),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
