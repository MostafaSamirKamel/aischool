<?php

namespace App\Filament\Admin\Resources\Vaults\Tables;

use App\Models\Vault;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class VaultsTable
{
    public static function configure(Table $table): Table
    {
        // التحقق من أن المستخدم ليس أدمن رئيسي
        $isSuperAdmin = auth()->user()->is_super_admin && is_null(auth()->user()->tenant_id);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/vault_resource.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cash' => '💵 نقدي',
                        'vodafone' => '📱 فودافون كاش',
                        'instapay' => '💳 إنستاباي',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'cash',
                        'warning' => 'vodafone',
                        'info' => 'instapay',
                    ]),

                TextColumn::make('opening_balance')
                    ->label(__('filament/admin/vault_resource.opening_balance'))
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn() => auth()->user()?->is_super_admin || auth()->user()?->can('view_vault_balances')),

                TextColumn::make('current_balance')
                    ->label(__('filament/admin/vault_resource.current_balance'))
                    ->state(function ($record) {
                        return $record->getCurrentBalance();
                    })
                    ->money('EGP')
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->visible(fn() => auth()->user()?->is_super_admin || auth()->user()?->can('view_vault_balances')),

                BadgeColumn::make('is_active')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => $state ? 'نشط' : 'غير نشط')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),

                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/vault_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visible(fn() => auth()->user()->is_super_admin ?? false),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/vault_resource.created_at'))
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('الفرع')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->is_super_admin ?? false),

                SelectFilter::make('type')
                    ->label('نوع الخزينة')
                    ->options([
                        'cash' => '💵 نقدي',
                        'vodafone' => '📱 فودافون كاش',
                        'instapay' => '💳 إنستاباي',
                    ]),

                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([
                        1 => 'نشط',
                        0 => 'غير نشط',
                    ]),
            ])
            ->recordActions($isSuperAdmin ? [] : [
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function (Vault $record): void {
                        $paymentsCount = $record->payments()->count();
                        if ($paymentsCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف الخزنة')
                                ->body("يوجد {$paymentsCount} مدفوعة مرتبطة بهذه الخزنة.")
                                ->send();

                            return;
                        }

                        $enrollmentsCount = $record->enrollments()->count();
                        if ($enrollmentsCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف الخزنة')
                                ->body("يوجد {$enrollmentsCount} تسجيل مرتبط بهذه الخزنة.")
                                ->send();

                            return;
                        }

                        $revenuesCount = $record->revenues()->count();
                        if ($revenuesCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف الخزنة')
                                ->body("يوجد {$revenuesCount} إيراد مرتبط بهذه الخزنة.")
                                ->send();

                            return;
                        }

                        $expensesCount = $record->expenses()->count();
                        if ($expensesCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف الخزنة')
                                ->body("يوجد {$expensesCount} مصروف مرتبط بهذه الخزنة.")
                                ->send();

                            return;
                        }

                        $salariesCount = $record->salaryPayments()->count();
                        if ($salariesCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف الخزنة')
                                ->body("يوجد {$salariesCount} صرف رواتب مرتبط بهذه الخزنة.")
                                ->send();

                            return;
                        }

                        try {
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title('تم حذف الخزنة بنجاح')
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->danger()
                                ->title('تعذر حذف الخزنة')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions($isSuperAdmin ? [] : [
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $deletedCount = 0;
                            $skippedCount = 0;

                            foreach ($records as $record) {
                                /** @var Vault $record */
                                $isBlocked = $record->payments()->exists()
                                    || $record->enrollments()->exists()
                                    || $record->revenues()->exists()
                                    || $record->expenses()->exists()
                                    || $record->salaryPayments()->exists();

                                if ($isBlocked) {
                                    $skippedCount++;
                                    continue;
                                }

                                try {
                                    $record->delete();
                                    $deletedCount++;
                                } catch (Throwable) {
                                    $skippedCount++;
                                }
                            }

                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('تم حذف الخزائن المسموح بها')
                                    ->body("تم حذف {$deletedCount} خزنة.")
                                    ->send();
                            }

                            if ($skippedCount > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('تم تخطي بعض الخزائن')
                                    ->body("تعذر حذف {$skippedCount} خزنة لوجود بيانات مرتبطة بها.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
