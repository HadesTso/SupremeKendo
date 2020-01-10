<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idfa extends Model
{
    protected $fillable = [
        'apple_id', 'idfa', 'ip', 'type',
    ];
}