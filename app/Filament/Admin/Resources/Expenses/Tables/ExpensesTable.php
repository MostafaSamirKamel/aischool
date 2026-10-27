<?php

namespace App\Filament\Admin\Resources\Expenses\Tables;

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

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_code')
                    ->label(__('filament/admin/expense_resource.expense_code'))
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
                    ->label(__('filament/admin/expense_resource.title'))
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
                    ->label(__('filament/admin/expense_resource.category.name'))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('amount')
                    ->label(__('filament/admin/expense_resource.amount'))
                    ->money('EGP')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('EGP')
                            ->label(__('filament/admin/expense_resource.total')),
                    ]),
                
                TextColumn::make('expense_date')
                    ->label(__('filament/admin/expense_resource.expense_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('vault.name')
                    ->label('الخزنة المصروف منها')
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
                        __("filament/admin/expense_resource.payment_methods.{$state}"))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('status')
                    ->label(__('filament/admin/expense_resource.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => 
                        __("filament/admin/expense_resource.statuses.{$state}")),
                
                TextColumn::make('vendor_name')
                    ->label(__('filament/admin/expense_resource.vendor_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('invoice_number')
                    ->label(__('filament/admin/expense_resource.invoice_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('createdBy.name')
                    ->label(__('filament/admin/expense_resource.created_by.name'))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('filament/admin/expense_resource.created_at'))
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
                    ->label(__('filament/admin/expense_resource.status'))
                    ->options([
                        'pending' => __('filament/admin/expense_resource.statuses.pending'),
                        'approved' => __('filament/admin/expense_resource.statuses.approved'),
                        'paid' => __('filament/admin/expense_resource.statuses.paid'),
                        'cancelled' => __('filament/admin/expense_resource.statuses.cancelled'),
                    ])
                    ->multiple(),
                
                SelectFilter::make('expense_category_id')
                    ->label(__('filament/admin/expense_resource.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('vault_id')
                    ->label('الخزنة المصروف منها')
                    ->relationship('vault', 'name', fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_active', true)->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid)))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع (تاريخي)')
                    ->options([
                        'cash' => __('filament/admin/expense_resource.payment_methods.cash'),
                        'bank_transfer' => __('filament/admin/expense_resource.payment_methods.bank_transfer'),
                        'card' => __('filament/admin/expense_resource.payment_methods.card'),
                        'cheque' => __('filament/admin/expense_resource.payment_methods.cheque'),
                        'other' => __('filament/admin/expense_resource.payment_methods.other'),
                    ])
                    ->multiple(),
                
                Filter::make('expense_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label(__('filament/admin/expense_resource.from_date')),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label(__('filament/admin/expense_resource.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
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
            ->defaultSort('expense_date', 'desc');
    }
}
