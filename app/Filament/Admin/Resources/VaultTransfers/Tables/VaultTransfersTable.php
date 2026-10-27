<?php

namespace App\Filament\Admin\Resources\VaultTransfers\Tables;

use App\Models\VaultTransfer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VaultTransfersTable
{
    public static function configure(Table $table): Table
    {
        $isSuperAdmin = auth()->user()->is_super_admin ?? false;

        return $table
            ->columns([
                TextColumn::make('transfer_code')
                    ->label('كود التحويل')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('transfer_date')
                    ->label('تاريخ التحويل')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fromVault.name')
                    ->label('من خزينة')
                    ->searchable()
                    ->badge()
                    ->color('danger'),

                TextColumn::make('toVault.name')
                    ->label('إلى خزينة')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '⏳ قيد الانتظار',
                        'completed' => '✅ مكتمل',
                        'cancelled' => '❌ ملغي',
                        default => $state,
                    }),

                TextColumn::make('transferredBy.name')
                    ->label('تم بواسطة')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approvedBy.name')
                    ->label('تم الاعتماد بواسطة')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label('تاريخ الاعتماد')
                    ->dateTime('d/m/Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => '⏳ قيد الانتظار',
                        'completed' => '✅ مكتمل',
                        'cancelled' => '❌ ملغي',
                    ]),

                SelectFilter::make('from_vault_id')
                    ->label('من خزينة')
                    ->relationship('fromVault', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('to_vault_id')
                    ->label('إلى خزينة')
                    ->relationship('toVault', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('transfer_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('من تاريخ')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('until')
                            ->label('إلى تاريخ')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transfer_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transfer_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'من تاريخ: ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'إلى تاريخ: ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions($isSuperAdmin ? [] : [
                ViewAction::make(),
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (VaultTransfer $record) {
                        $fromBalance = $record->fromVault?->getCurrentBalance() ?? 0.0;
                        if ($fromBalance < (float) $record->amount) {
                            Notification::make()
                                ->title('فشل اعتماد التحويل')
                                ->body('رصيد الخزنة المحول منها غير كافٍ. الرصيد الحالي: ' . number_format($fromBalance, 2) . ' ج.م')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'status' => 'completed',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('تم اعتماد التحويل بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (VaultTransfer $record): bool => $record->isPending()),

                EditAction::make()
                    ->visible(fn (VaultTransfer $record): bool => $record->canBeEdited()),

                Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (VaultTransfer $record, array $data) {
                        $record->update([
                            'status' => 'cancelled',
                            'cancellation_reason' => $data['cancellation_reason'],
                        ]);

                        Notification::make()
                            ->title('تم إلغاء التحويل')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (VaultTransfer $record): bool => $record->isPending()),

                DeleteAction::make()
                    ->visible(fn (VaultTransfer $record): bool => $record->canBeDeleted())
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                // يمكن إضافة bulk actions هنا
            ]);
    }
}
