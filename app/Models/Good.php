<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Good extends Model
{
    public $fillable = ['id', 'good_name'];

    public $timestamps = false;
}