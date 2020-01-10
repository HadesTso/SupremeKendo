<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpOperation extends Model
{
    protected $fillable = [
        'ip', 'status'
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