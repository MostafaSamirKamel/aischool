<?php

namespace App\Filament\Admin\Resources\Students\Pages;

use App\Filament\Admin\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_student.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء طلاب')
                ->danger()
                ->send();

            $this->redirect(StudentResource::getUrl('index'));
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
            'tenant_id' => auth()->user()->tenant_id,
            'status' => 'active',
        ]);

        // Assign student role to the user
        $user->assignRole('student');

        // Replace user fields with user_id
        $data['user_id'] = $user->id;
        $data['tenant_id'] = auth()->user()->tenant_id;

        // Remove temporary fields
        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Log all form data for debugging
        \Log::info('=== Student Creation - All Form Data ===', [
            'all_data' => $this->data,
            'has_parents_key' => isset($this->data['parents']),
            'parents_value' => $this->data['parents'] ?? 'NOT SET',
        ]);

        // Process parent relationships
        $parents = $this->data['parents'] ?? [];

        // إذا كان المصفوفة فاضية أو null، نوقف التنفيذ
        if (empty($parents) || !is_array($parents)) {
            \Log::info('No parents data found or not an array', ['parents' => $parents]);
            return;
        }

        \Log::info('Processing parents', ['count' => count($parents), 'parents' => $parents]);

        foreach ($parents as $index => $parentData) {
            \Log::info("Processing parent #{$index}", ['data' => $parentData]);

            // تخطي إذا كانت البيانات فاضية أو ليست array
            if (empty($parentData) || !is_array($parentData)) {
                \Log::warning("Parent #{$index} skipped - empty or not array");
                continue;
            }

            $parentUserId = null;

            // Check if new parent needs to be created
            if (isset($parentData['is_new_parent']) && $parentData['is_new_parent'] === true) {
                \Log::info("Creating new parent user", ['data' => $parentData]);

                // تحقق من وجود البيانات المطلوبة
                if (
                    empty($parentData['new_parent_name']) ||
                    empty($parentData['new_parent_email']) ||
                    empty($parentData['new_parent_password'])
                ) {
                    \Log::warning('Parent creation skipped - missing required fields', $parentData);
                    continue;
                }

                try {
                    // Create new parent user
                    $parentUser = User::create([
                        'name' => $parentData['new_parent_name'],
                        'username' => $parentData['new_parent_username'] ?? null,
                        'email' => $parentData['new_parent_email'],
                        'password' => Hash::make($parentData['new_parent_password']),
                        'phone' => $parentData['new_parent_phone'] ?? null,
                        'tenant_id' => auth()->user()->tenant_id,
                        'status' => 'active',
                    ]);

                    // Assign parent role
                    $parentUser->assignRole('parent');

                    // Use the created parent user ID
                    $parentUserId = $parentUser->id;

                    \Log::info('Parent user created successfully', ['user_id' => $parentUserId]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create parent user', [
                        'error' => $e->getMessage(),
                        'data' => $parentData
                    ]);
                    continue;
                }
            } else {
                // Use existing parent ID
                $parentUserId = $parentData['parent_user_id'] ?? null;
                \Log::info('Using existing parent', ['parent_user_id' => $parentUserId]);
            }

            // التحقق من أن relationship_type موجود
            if (empty($parentData['relationship_type'])) {
                \Log::warning('Parent relationship skipped - missing relationship_type', [
                    'parent_user_id' => $parentUserId,
                    'parent_data' => $parentData
                ]);
                continue;
            }

            // Create student-parent relationship only if we have a valid parent user ID
            if (!empty($parentUserId) && is_numeric($parentUserId)) {
                \Log::info('Creating student-parent relationship', [
                    'student_id' => $this->record->id,
                    'parent_user_id' => $parentUserId,
                    'relation' => $parentData['relationship_type'],
                ]);

                try {
                    $this->record->studentParents()->create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'user_id' => $parentUserId,
                        'relation' => $parentData['relationship_type'],
                        'is_primary' => $parentData['is_primary_guardian'] ?? false,
                        'can_pickup' => $parentData['has_pickup_permission'] ?? true,
                        'receive_notifications' => $parentData['can_receive_notifications'] ?? true,
                        'notes' => $parentData['notes'] ?? null,
                    ]);

                    \Log::info('Student-parent relationship created successfully');
                } catch (\Exception $e) {
                    \Log::error('Failed to create student-parent relationship', [
                        'error' => $e->getMessage(),
                        'student_id' => $this->record->id,
                        'parent_user_id' => $parentUserId,
                        'data' => $parentData
                    ]);
                    throw $e; // Re-throw to see the actual error
                }
            } else {
                \Log::warning('Parent relationship skipped - invalid parent_user_id', [
                    'parent_user_id' => $parentUserId,
                    'is_empty' => empty($parentUserId),
                    'is_numeric' => is_numeric($parentUserId),
                    'parent_data' => $parentData
                ]);
            }
        }
    }

    public function getTitle(): string
    {
        return __('filament/admin/create_student.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_student.title');
    }

}
