<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteIp extends Model
{
    protected $fillable = [
        'ip'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    public function server()
    {
        return $this->hasOne(Server::class, 'id', 'server_id');
    }
}