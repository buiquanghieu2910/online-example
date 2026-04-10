<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSetting extends Model
{
    protected $fillable = [
        'enabled',
        'message',
        'updated_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}

