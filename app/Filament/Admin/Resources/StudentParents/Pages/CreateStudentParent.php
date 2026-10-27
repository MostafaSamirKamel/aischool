<?php

namespace App\Filament\Admin\Resources\StudentParents\Pages;

use App\Filament\Admin\Resources\StudentParents\StudentParentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class CreateStudentParent extends CreateRecord
{
    protected static string $resource = StudentParentResource::class;
    
    public function mount(): void
    {
        parent::mount();
        
        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_student_parent.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();
                
            $this->redirect(StudentParentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create user account first
        $user = User::create([
            'name' => $data['user_name'],
            'username' => $data['user_username'] ?? null,
            'email' => $data['user_email'],
            'password' => Hash::make($data['user_password']),
            'phone' => $data['user_phone'] ?? null,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        // Assign parent role to the user
        $user->assignRole('parent');

        // Replace user fields with user_id
        $data['user_id'] = $user->id;
        $data['tenant_id'] = auth()->user()->tenant_id;

        // Remove temporary fields
        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_phone']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_student_parent.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_student_parent.title');
    }

}
