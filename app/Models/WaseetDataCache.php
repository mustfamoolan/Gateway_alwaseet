<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaseetDataCache extends Model
{
    protected $table = 'waseet_data_cache';

    protected $fillable = [
        'type',
        'external_id',
        'parent_id',
        'name',
        'last_updated_at'
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];
}
