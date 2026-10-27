<?php

namespace App\Filament\Admin\Resources\Students\Pages;

use App\Filament\Admin\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_student.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل الطلاب')
                ->danger()
                ->send();

            $this->redirect(StudentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fill user data
        if ($this->record->user) {
            $data['user_name'] = $this->record->user->name;
            $data['user_email'] = $this->record->user->email;
            $data['user_username'] = $this->record->user->username;
        }

        // Load parent relationships data
        $parents = [];

        foreach ($this->record->studentParents as $studentParent) {
            $parent = $studentParent->parent;

            if ($parent) {
                $parents[] = [
                    'is_new_parent' => false,
                    'parent_user_id' => $parent->id,
                    'new_parent_name' => null,
                    'new_parent_email' => null,
                    'new_parent_password' => null,
                    'new_parent_phone' => null,
                    'relationship_type' => $studentParent->relation,
                    'is_primary_guardian' => $studentParent->is_primary,
                    'has_pickup_permission' => $studentParent->can_pickup,
                    'can_view_grades' => true, // default value
                    'can_receive_notifications' => $studentParent->receive_notifications,
                    'notes' => $studentParent->notes,
                ];
            }
        }

        $data['parents'] = $parents;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update user data if changed
        if ($this->record->user) {
            $userData = [];

            if (isset($data['user_name'])) {
                $userData['name'] = $data['user_name'];
                unset($data['user_name']);
            }

            if (isset($data['user_email'])) {
                $userData['email'] = $data['user_email'];
                unset($data['user_email']);
            }

            if (isset($data['user_username'])) {
                $userData['username'] = $data['user_username'];
                unset($data['user_username']);
            }

            if (isset($data['user_password']) && filled($data['user_password'])) {
                $userData['password'] = Hash::make($data['user_password']);
            }
            unset($data['user_password']);

            if (!empty($userData)) {
                $this->record->user->update($userData);
            }
        }

        // Process parent updates - delete old relationships and create new ones
        $parents = $data['parents'] ?? [];

        // حذف جميع العلاقات القديمة
        $this->record->studentParents()->delete();

        // إعادة إنشاء العلاقات
        foreach ($parents as $parentData) {
            if (empty($parentData) || !is_array($parentData)) {
                continue;
            }

            $parentUserId = null;

            // Check if new parent needs to be created
            if (isset($parentData['is_new_parent']) && $parentData['is_new_parent'] === true) {
                if (
                    empty($parentData['new_parent_name']) ||
                    empty($parentData['new_parent_email']) ||
                    empty($parentData['new_parent_password'])
                ) {
                    continue;
                }

                try {
                    // Create new parent user
                    $parentUser = User::create([
                        'name' => $parentData['new_parent_name'],
                        'email' => $parentData['new_parent_email'],
                        'password' => Hash::make($parentData['new_parent_password']),
                        'phone' => $parentData['new_parent_phone'] ?? null,
                        'tenant_id' => auth()->user()->tenant_id,
                        'status' => 'active',
                    ]);

                    $parentUser->assignRole('parent');
                    $parentUserId = $parentUser->id;
                } catch (\Exception $e) {
                    \Log::error('Failed to create parent user in edit', [
                        'error' => $e->getMessage(),
                        'data' => $parentData
                    ]);
                    continue;
                }
            } else {
                $parentUserId = $parentData['parent_user_id'] ?? null;
            }

            if (empty($parentData['relationship_type'])) {
                continue;
            }

            if (!empty($parentUserId) && is_numeric($parentUserId)) {
                try {
                    $this->record->studentParents()->create([
                        'user_id' => $parentUserId,
                        'relation' => $parentData['relationship_type'],
                        'is_primary' => $parentData['is_primary_guardian'] ?? false,
                        'can_pickup' => $parentData['has_pickup_permission'] ?? true,
                        'receive_notifications' => $parentData['can_receive_notifications'] ?? true,
                        'notes' => $parentData['notes'] ?? null,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create student-parent relationship in edit', [
                        'error' => $e->getMessage(),
                        'student_id' => $this->record->id,
                        'parent_user_id' => $parentUserId,
                    ]);
                }
            }
        }

        // Remove parents from data before saving student
        unset($data['parents']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
            return [];
        }

        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_student.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_student.title');
    }

}
