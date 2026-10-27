<?php

namespace App\Filament\Admin\Resources\Instructors\Pages;

use App\Filament\Admin\Resources\Instructors\InstructorResource;
use Filament\Notifications\Notification;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditInstructor extends EditRecord
{
    protected static string $resource = InstructorResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        $data['user_name'] = $user?->name;
        $data['user_username'] = $user?->username;
        $data['user_email'] = $user?->email;
        $data['user_phone'] = $user?->phone;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;

        if ($user) {
            $userData = [
                'name' => $data['user_name'] ?? $user->name,
                'username' => $data['user_username'] ?? $user->username,
                'email' => $data['user_email'] ?? $user->email,
                'phone' => $data['user_phone'] ?? $user->phone,
            ];

            if (!empty($data['user_password'])) {
                $userData['password'] = Hash::make($data['user_password']);
            }

            $user->update($userData);
        }

        unset(
            $data['user_name'],
            $data['user_username'],
            $data['user_email'],
            $data['user_password'],
            $data['user_phone']
        );

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('تم تحديث بيانات المدرس بنجاح')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه حذف مدرسين
        if (!$user->is_super_admin) {
            $actions[] = DeleteAction::make();
            $actions[] = ForceDeleteAction::make();
            $actions[] = RestoreAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_instructor.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_instructor.title');
    }

}
