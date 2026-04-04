<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'project_id',
        'endpoint',
        'request_payload',
        'response_payload',
        'status',
        'http_status_code',
        'ip_address'
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
