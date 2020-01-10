<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gmmail extends Model
{
    protected $fillable = [
        'role_list', 'server_id', 'channel_id', 'title', 'content', 'attach_s', 'account_id'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    public function server()
    {
        return $this->hasOne(Server::class, 'id', 'server_id');
    }

    public function channel()
    {
        return $this->hasOne(Channel::class, 'id', 'channel_id');
    }
}