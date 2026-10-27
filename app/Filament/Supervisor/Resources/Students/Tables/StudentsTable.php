<?php

namespace App\Filament\Supervisor\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.profile_photo')
                    ->label(__('filament/supervisor/student_resource.user.profile_photo'))
                    ->circular(),

                TextColumn::make('user.name')
                    ->label(__('filament/supervisor/student_resource.user.name'))
                    ->formatStateUsing(fn($record) => $record->student_code . ' - ' . $record->user->name)
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($query) use ($search) {
                            $query->where('student_code', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label(__('filament/supervisor/student_resource.user.email'))
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('user.phone')
                    ->label(__('filament/supervisor/student_resource.user.phone'))
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('phone', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('groups_count')
                    ->label(__('filament/supervisor/student_resource.groups_count'))
                    ->counts('groups')
                    ->badge()
                    ->color('info'),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                        default => $state,
                    }),

                TextColumn::make('enrollment_date')
                    ->label(__('filament/supervisor/student_resource.enrollment_date'))
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
