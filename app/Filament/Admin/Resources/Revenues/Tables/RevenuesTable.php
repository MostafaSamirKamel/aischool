<?php

namespace App\Filament\Admin\Resources\Revenues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class RevenuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('revenue_code')
                    ->label(__('filament/admin/revenue_resource.revenue_code'))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('tenant.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),

                TextColumn::make('number')
                    ->weight(FontWeight::Bold)
                    ->copyable(),
                
                TextColumn::make('title')
                    ->label(__('filament/admin/revenue_resource.title'))
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                
                TextColumn::make('category.name')
                    ->label(__('filament/admin/revenue_resource.category.name'))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('amount')
                    ->label(__('filament/admin/revenue_resource.amount'))
                    ->money('EGP')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('EGP')
                            ->label(__('filament/admin/revenue_resource.total')),
                    ]),
                
                TextColumn::make('revenue_date')
                    ->label(__('filament/admin/revenue_resource.revenue_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('vault.name')
                    ->label('الخزنة المستلمة')
                    ->badge()
                    ->formatStateUsing(fn($record) => match ($record->vault?->type) {
                        'cash' => '💵 ' . $record->vault->name,
                        'vodafone' => '📱 ' . $record->vault->name,
                        'instapay' => '💳 ' . $record->vault->name,
                        default => '💰 ' . ($record->vault?->name ?? '')
                    })
                    ->color('success')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع (تاريخي)')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => 
                        __("filament/admin/revenue_resource.payment_methods.{$state}"))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('status')
                    ->label(__('filament/admin/revenue_resource.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => 
                        __("filament/admin/revenue_resource.statuses.{$state}")),
                
                TextColumn::make('customer_name')
                    ->label(__('filament/admin/revenue_resource.customer_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('invoice_number')
                    ->label(__('filament/admin/revenue_resource.invoice_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('payment.id')
                    ->label(__('filament/admin/revenue_resource.payment.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->payment ? route('filament.admin.resources.payments.edit', $record->payment) : null),
                
                TextColumn::make('createdBy.name')
                    ->label(__('filament/admin/revenue_resource.created_by.name'))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('filament/admin/revenue_resource.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                
                SelectFilter::make('tenant_id')
                    ->label('الفرع')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
SelectFilter::make('status')
                    ->label(__('filament/admin/revenue_resource.status'))
                    ->options([
                        'pending' => __('filament/admin/revenue_resource.statuses.pending'),
                        'received' => __('filament/admin/revenue_resource.statuses.received'),
                        'cancelled' => __('filament/admin/revenue_resource.statuses.cancelled'),
                    ])
                    ->multiple(),
                
                SelectFilter::make('revenue_category_id')
                    ->label(__('filament/admin/revenue_resource.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('vault_id')
                    ->label('الخزنة المستلمة')
                    ->relationship('vault', 'name', fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_active', true)->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid)))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع (تاريخي)')
                    ->options([
                        'cash' => __('filament/admin/revenue_resource.payment_methods.cash'),
                        'bank_transfer' => __('filament/admin/revenue_resource.payment_methods.bank_transfer'),
                        'card' => __('filament/admin/revenue_resource.payment_methods.card'),
                        'online' => __('filament/admin/revenue_resource.payment_methods.online'),
                        'other' => __('filament/admin/revenue_resource.payment_methods.other'),
                    ])
                    ->multiple(),
                
                Filter::make('revenue_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label(__('filament/admin/revenue_resource.from_date')),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label(__('filament/admin/revenue_resource.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('revenue_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('revenue_date', '<=', $date),
                            );
                    }),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('revenue_date', 'desc');
    }
}
