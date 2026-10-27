<?php

namespace App\Filament\Admin\Resources\Payments\Tables;

use App\Filament\Exports\PaymentExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_code')
                    ->label(__('filament/admin/payment_resource.payment_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('enrollment.enrollment_code')
                    ->label(__('filament/admin/payment_resource.enrollment.enrollment_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('enrollment.student.user.name')
                    ->label(__('filament/admin/payment_resource.enrollment.student.user.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vault.name')
                    ->label('الخزنة المستلمة')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد'),

                TextColumn::make('amount')
                    ->label(__('filament/admin/payment_resource.amount'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label(__('filament/admin/payment_resource.payment_date'))
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament/admin/payment_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('receivedBy.name')
                    ->label(__('filament/admin/payment_resource.received_by.name'))
                    ->default('غير محدد')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/payment_resource.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),

                SelectFilter::make('vault_id')
                    ->label('الخزنة المستلمة')
                    ->relationship('vault', 'name', fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_active', true)->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid)))
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => !auth()->user()->is_super_admin),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(PaymentExporter::class)
                    ->label('تصدير')
                    ->color('success'),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => !auth()->user()->is_super_admin),
                    RestoreBulkAction::make()
                        ->visible(fn () => !auth()->user()->is_super_admin),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
