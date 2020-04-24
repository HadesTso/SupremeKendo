<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string rid
 * @property array|null|string code
 * @property array|null|string cid
 * @property array|null|string sid
 * @property array|null|string status
 */
class RoleUseCode extends Model
{
    public $timestamps = false;
}