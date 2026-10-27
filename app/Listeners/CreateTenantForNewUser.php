<?php

namespace App\Listeners;

use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateTenantForNewUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        
        // إنشاء tenant جديد باسم المستخدم
        $tenant = Tenant::create([
            'name' => $user->name . ' Clinic',
            'code' => strtolower(str_replace(' ', '-', $user->name)) . '-' . uniqid(),
        ]);
        
        // ربط المستخدم بالـ tenant
        $user->update([
            'tenant_id' => $tenant->id,
            'is_tenant_owner' => true, // جعله مالك الـ tenant
        ]);
    }
}
