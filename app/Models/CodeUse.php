<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed code_id
 * @property mixed code
 * @property array|null|string role_id
 * @property mixed code_box_id
 */
class CodeUse extends Model
{
    protected $fillable = [
        'code'
    ];
}