<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = [
        'role_id', 'serverId', 'status', 'type', 'reason'
    ];
}