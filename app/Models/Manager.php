<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string manager_name
 * @property array|null|string remark
 * @property array|null|string status
 * @property array|null|string menu
 */
class Manager extends Model
{
    protected $fillable = [
        'manager_name', 'status', 'remark', 'menu'
    ];
}