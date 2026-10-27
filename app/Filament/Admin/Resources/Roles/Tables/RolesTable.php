<?php

namespace App\Filament\Admin\Resources\Roles\Tables;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

class RolesTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->weight(FontWeight::Medium)
                    ->label('اسم الدور')
                    ->formatStateUsing(fn(string $state): string => Str::headline($state))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('panel_type')
                    ->label('نوع البانل')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'admin' => 'danger',
                        'instructor' => 'warning',
                        'student' => 'success',
                        'parent' => 'info',
                        'supervisor' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => $state ? match ($state) {
                        'admin' => '🔐 Admin',
                        'instructor' => '👨‍🏫 Instructor',
                        'student' => '🎓 Student',
                        'parent' => '👨‍👩‍👧 Parent',
                        'supervisor' => '👁️ Supervisor',
                        default => $state,
                    } : 'إداري')
                    ->sortable(),
                    
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label('Guard')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('permissions_count')
                    ->badge()
                    ->label('عدد الصلاحيات')
                    ->counts('permissions')
                    ->color('primary')
                    ->sortable(),
                    
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
