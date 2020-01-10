<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'account_name', 'password', 'slat', 'channel', 'status'
    ];

    public function manager()
    {
        return $this->hasOne(Manager::class, 'id', 'manager_id');
    }
}