<?php

namespace App\Filament\Admin\Resources\SalaryPayments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalaryPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('employee_type')
                    ->label('نوع الموظف')
                    ->formatStateUsing(fn ($state) => $state === 'instructor' ? 'مهندس/مدرس' : 'سكرتارية/إداري')
                    ->colors([
                        'info' => 'instructor',
                        'success' => 'admin',
                    ]),

                TextColumn::make('employee_name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('تاريخ الصرف')
                    ->date('Y-m-d')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),

                TextColumn::make('vault.name')
                    ->label('الخزنة المصروف منها')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('أنشئ بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_type')
                    ->label('نوع الموظف')
                    ->options([
                        'instructor' => 'مهندس/مدرس',
                        'admin' => 'سكرتارية/إداري',
                    ]),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'cancelled' => 'ملغي',
                    ]),

                SelectFilter::make('vault_id')
                    ->label('الخزنة المصروف منها')
                    ->relationship('vault', 'name', fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_active', true)->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid)))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('صرف')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'paid')
                    ->fillForm(fn ($record) => [
                        'vault_id' => $record->vault_id,
                    ])
                    ->form([
                        \Filament\Forms\Components\Select::make('vault_id')
                            ->label('الخزنة المصروف منها')
                            ->options(function () {
                                $user = auth()->user();
                                return \App\Models\Vault::query()
                                    ->where('is_active', true)
                                    ->when($user?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid))
                                    ->get()
                                    ->mapWithKeys(fn ($v) => [$v->id => $v->getDisplayNameWithBalance()]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $vaultId = $data['vault_id'] ?? $record->vault_id;
                        $record->update([
                            'vault_id' => $vaultId,
                            'status' => 'paid',
                            'payment_date' => $record->payment_date ?? now(),
                            'paid_by' => auth()->id(),
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }
}
