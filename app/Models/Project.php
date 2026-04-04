<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'api_key',
        'waseet_username',
        'waseet_password',
        'waseet_token',
        'waseet_token_refresh_at',
        'is_active',
        'description'
    ];

    protected $hidden = [
        'waseet_password',
    ];

    protected $casts = [
        'waseet_token_refresh_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function requestLogs()
    {
        return $this->hasMany(RequestLog::class);
    }
}
