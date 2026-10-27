<?php

namespace App\Filament\Admin\Resources\StudentParents\Pages;

use App\Filament\Admin\Resources\StudentParents\StudentParentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStudentParent extends EditRecord
{
    protected static string $resource = StudentParentResource::class;
    
    public function mount($record): void
    {
        parent::mount($record);
        
        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_student_parent.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();
                
            $this->redirect(StudentParentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load parent user data into form
        $user = $this->record->parent;

        if ($user) {
            $data['user_name'] = $user->name;
            $data['user_email'] = $user->email;
            $data['user_phone'] = $user->phone;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update parent user data
        $user = $this->record->parent;

        if ($user) {
            $updateData = [
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'phone' => $data['user_phone'] ?? null,
            ];

            // Only update password if provided
            if (!empty($data['user_password'])) {
                $updateData['password'] = \Hash::make($data['user_password']);
            }

            $user->update($updateData);
        }

        // Remove user fields from data before saving StudentParent
        unset($data['user_name'], $data['user_email'], $data['user_password'], $data['user_phone']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
            return [];
        }
        
        return [
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('filament/admin/edit_student_parent.title');
    }

    public function getHeading(): string
    {
        return __('filament/admin/edit_student_parent.title');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_student_parent.title');
    }

}
