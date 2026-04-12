<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WaProject extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'owner_name',
        'phone_number',
        'api_key',
        'status',
        'session_data',
    ];

    /**
     * Generate a unique API Key when creating a project.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($project) {
            if (empty($project->api_key)) {
                $project->api_key = 'wa_live_' . Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class);
    }
}
