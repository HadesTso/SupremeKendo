<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeBatch extends Model
{
    protected $fillable = [
        'batch_name', 'batch_detail', 'code_box_id', 'code_prefix',
        'code_length', 'platform', 'channel_id', 'server_id',
        'use_count', 'account_id', 'start_time', 'end_time',
        'channel_id', 'server_id'
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

    public function codeBox()
    {
        return $this->hasOne(CodeBox::class, 'id', 'code_box_id');
    }
}