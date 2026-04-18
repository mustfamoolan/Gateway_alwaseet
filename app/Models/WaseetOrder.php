<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaseetOrder extends Model
{
    protected $fillable = [
        'project_id',
        'waseet_order_id',
        'last_status',
        'is_terminal',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
