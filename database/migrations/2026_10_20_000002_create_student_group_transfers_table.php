<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure enrollments table has group_id column
        if (!Schema::hasColumn('enrollments', 'group_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            });
        }

        // 2. Create student_group_transfers table
        if (!Schema::hasTable('student_group_transfers')) {
            Schema::create('student_group_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
                $table->foreignId('from_group_id')->nullable()->constrained('groups')->nullOnDelete();
                $table->foreignId('to_group_id')->constrained('groups')->cascadeOnDelete();
                $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->boolean('progress_warning_acknowledged')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamp('transferred_at')->useCurrent();
                $table->timestamps();

                $table->index(['student_id', 'enrollment_id']);
                $table->index('tenant_id');
                $table->index('transferred_at');
            });
        }

        // 3. Register the transfer_student_group permission
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $permission = Permission::firstOrCreate(['name' => 'transfer_student_group', 'guard_name' => 'web']);
            
            $superAdmin = Role::where('name', 'super_admin')->first();
            if ($superAdmin && !$superAdmin->hasPermissionTo('transfer_student_group')) {
                $superAdmin->givePermissionTo($permission);
            }

            $admin = Role::where('name', 'admin')->first();
            if ($admin && !$admin->hasPermissionTo('transfer_student_group')) {
                $admin->givePermissionTo($permission);
            }
        } catch (\Throwable $e) {
            // In case roles are not yet seeded or during testing
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_group_transfers');

        try {
            $permission = Permission::where('name', 'transfer_student_group')->first();
            if ($permission) {
                $permission->delete();
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
