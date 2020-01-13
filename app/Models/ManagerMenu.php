<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerMenu extends Model
{
    protected $fillable = [
        'manager_id', 'menu_id',
    ];
}