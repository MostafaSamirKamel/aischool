<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentMessage;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParentMessagesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_messages_table_renders_without_attempt_to_read_property_on_null(): void
    {
        $tenant = Tenant::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $parentUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $parentUser->assignRole('parent');

        $studentUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Child',
        ]);

        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'tenant_id' => $tenant->id,
        ]);

        StudentParent::create([
            'tenant_id' => $tenant->id,
            'user_id' => $parentUser->id,
            'student_id' => $student->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);

        // Create unread message
        StudentMessage::create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'sender_id' => $parentUser->id,
            'subject' => 'رسالة تجريبية هامة',
            'message' => 'نص الرسالة الموجهة لولي الأمر والطلبة',
            'type' => 'info',
            'priority' => 'normal',
            'status' => 'sent',
            'is_read' => false,
            'sent_at' => now(),
        ]);

        $this->actingAs($parentUser);
        Filament::setCurrentPanel(Filament::getPanel('parent'));

        Livewire::test(\App\Filament\Parent\Resources\Messages\Pages\ListMessages::class)
            ->assertSuccessful()
            ->assertSee('رسالة تجريبية هامة')
            ->assertSee('الحالة');
    }
}
