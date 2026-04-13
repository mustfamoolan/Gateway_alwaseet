<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaMessage extends Model
{
    protected $fillable = [
        'wa_project_id',
        'from_number',
        'to_number',
        'message_body',
        'direction',
        'status',
        'error_message',
        'response_metadata'
    ];

    protected $casts = [
        'response_metadata' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(WaProject::class, 'wa_project_id');
    }
}
