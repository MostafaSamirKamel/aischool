<?php

namespace App\Filament\Admin\Resources\VaultReconciliations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VaultReconciliationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reconciliation_date')
                    ->label(__('filament/admin/vault_reconciliation_resource.reconciliation_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('vault.name')
                    ->label(__('filament/admin/vault_reconciliation_resource.vault.name'))
                    ->badge()
                    ->formatStateUsing(fn($record) => match ($record->vault?->type) {
                        'cash' => '💵 ' . $record->vault->name,
                        'vodafone' => '📱 ' . $record->vault->name,
                        'instapay' => '💳 ' . $record->vault->name,
                        default => '💰 ' . ($record->vault?->name ?? '')
                    })
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('system_balance')
                    ->label(__('filament/admin/vault_reconciliation_resource.system_balance'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('actual_balance')
                    ->label(__('filament/admin/vault_reconciliation_resource.actual_balance'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('difference')
                    ->label(__('filament/admin/vault_reconciliation_resource.difference'))
                    ->money('EGP')
                    ->color(fn ($state): string => 
                        $state == 0 ? 'success' : ($state > 0 ? 'info' : 'danger')
                    )
                    ->icon(fn ($state): string => 
                        $state == 0 ? 'heroicon-o-check-circle' : 
                        ($state > 0 ? 'heroicon-o-arrow-up' : 'heroicon-o-arrow-down')
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament/admin/vault_reconciliation_resource.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد المراجعة',
                        'approved' => 'معتمدة',
                        'rejected' => 'مرفوضة',
                        default => $state,
                    }),

                TextColumn::make('reconciledBy.name')
                    ->label(__('filament/admin/vault_reconciliation_resource.reconciled_by.name'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('approvedBy.name')
                    ->label(__('filament/admin/vault_reconciliation_resource.approved_by.name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label(__('filament/admin/vault_reconciliation_resource.approved_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vault_id')
                    ->label('الخزينة')
                    ->relationship('vault', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'معتمدة',
                        'rejected' => 'مرفوضة',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn ($record) => $record->status !== 'pending'),
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status !== 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status !== 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reconciliation_date', 'desc');
    }
}
