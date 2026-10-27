<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
    ];

    // العلاقات
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
}
