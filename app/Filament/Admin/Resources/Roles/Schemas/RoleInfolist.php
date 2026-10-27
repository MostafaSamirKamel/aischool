<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الدور')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('اسم الدور')
                                    ->badge()
                                    ->color('success'),
                                    
                                TextEntry::make('panel_type')
                                    ->label('نوع البانل')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'admin' => 'danger',
                                        'instructor' => 'success',
                                        'student' => 'info',
                                        'parent' => 'warning',
                                        'supervisor' => 'primary',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => $state ? match ($state) {
                                        'admin' => '🔐 Admin Panel',
                                        'instructor' => '👨‍🏫 Instructor Panel',
                                        'student' => '🎓 Student Panel',
                                        'parent' => '👨‍👩‍👧 Parent Panel',
                                        'supervisor' => '👁️ Supervisor Panel',
                                        default => $state,
                                    } : 'إداري')
                                    ->placeholder('إداري'),
                                    
                                TextEntry::make('guard_name')
                                    ->label('Guard')
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ]),
                    
                Section::make('الصلاحيات')
                    ->description('قائمة بجميع الصلاحيات المرتبطة بهذا الدور')
                    ->schema([
                        TextEntry::make('permissions.name')
                            ->label('')
                            ->badge()
                            ->color('primary')
                            ->separator(',')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                // تنسيق اسم الصلاحية لجعلها أكثر قابلية للقراءة
                                return str_replace('_', ' ', ucwords($state, '_'));
                            })
                            ->placeholder('لا توجد صلاحيات محددة'),
                    ])
                    ->collapsible(),
            ]);
    }
}
