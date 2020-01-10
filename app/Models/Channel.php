<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'id', 'channel_name', 'channel_abbr', 'principal_id', 'status'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'principal_id');
    }
}