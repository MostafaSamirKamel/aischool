<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'slug',
        'domain',
        'description',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'json',
    ];

    protected static function booted()
    {
        static::creating(function ($tenant) {
            if (empty($tenant->slug) && !empty($tenant->name)) {
                $slug = \Illuminate\Support\Str::slug($tenant->name);
                $tenant->slug = !empty($slug) ? $slug : 'tenant-' . uniqid();
            }
        });
    }

    /**
     * Get all users for this tenant
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the tenant name attribute for Filament
     */
    public function getTenantNameAttribute()
    {
        return $this->name ?? 'Tenant';
    }
}
