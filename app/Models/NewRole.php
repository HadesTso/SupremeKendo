<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewRole extends Model
{
    protected $fillable = [
        'title', 'content', 'attach_s', 'status', 'account_id'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }
}