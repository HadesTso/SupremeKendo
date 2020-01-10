<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'title', 'content', 'note', 'channel_id', 'status'
    ];

    public function server()
    {
        return $this->hasOne(Server::class, 'id', 'server_id');
    }

    public function channel()
    {
        return $this->hasOne(Channel::class, 'id', 'channel_id');
    }
}