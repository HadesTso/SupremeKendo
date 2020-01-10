<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeBox extends Model
{
    protected $fillable = [
        'box_name', 'box_item_list', 'account_id'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }
}