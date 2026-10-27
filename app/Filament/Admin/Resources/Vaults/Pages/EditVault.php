<?php

namespace App\Filament\Admin\Resources\Vaults\Pages;

use App\Filament\Admin\Resources\Vaults\VaultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Throwable;

class EditVault extends EditRecord
{
    protected static string $resource = VaultResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // منع الأدمن الرئيسي من التعديل
        if (auth()->user()->is_super_admin && is_null(auth()->user()->tenant_id)) {
            Notification::make()
                ->title(__('filament/admin/edit_vault.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل الخزائن')
                ->danger()
                ->send();

            $this->redirect(VaultResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        // إخفاء زر الحذف عن الأدمن الرئيسي
        if (auth()->user()->is_super_admin && is_null(auth()->user()->tenant_id)) {
            return [];
        }

        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->action(function (): void {
                    $vault = $this->record;

                    $paymentsCount = $vault->payments()->count();
                    if ($paymentsCount > 0) {
                        Notification::make()
                            ->danger()
                            ->title('لا يمكن حذف الخزنة')
                            ->body("يوجد {$paymentsCount} مدفوعة مرتبطة بهذه الخزنة.")
                            ->send();

                        return;
                    }

                    $enrollmentsCount = $vault->enrollments()->count();
                    if ($enrollmentsCount > 0) {
                        Notification::make()
                            ->danger()
                            ->title('لا يمكن حذف الخزنة')
                            ->body("يوجد {$enrollmentsCount} تسجيل مرتبط بهذه الخزنة.")
                            ->send();

                        return;
                    }

                    $revenuesCount = $vault->revenues()->count();
                    if ($revenuesCount > 0) {
                        Notification::make()
                            ->danger()
                            ->title('لا يمكن حذف الخزنة')
                            ->body("يوجد {$revenuesCount} إيراد مرتبط بهذه الخزنة.")
                            ->send();

                        return;
                    }

                    $expensesCount = $vault->expenses()->count();
                    if ($expensesCount > 0) {
                        Notification::make()
                            ->danger()
                            ->title('لا يمكن حذف الخزنة')
                            ->body("يوجد {$expensesCount} مصروف مرتبط بهذه الخزنة.")
                            ->send();

                        return;
                    }

                    try {
                        $vault->delete();

                        Notification::make()
                            ->success()
                            ->title('تم حذف الخزنة بنجاح')
                            ->send();

                        $this->redirect(VaultResource::getUrl('index'));
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('تعذر حذف الخزنة')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_vault.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_vault.title');
    }

}
