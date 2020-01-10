<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $fillable = [
        'code', 'code_batch_id', 'code_box_id', 'code_prefix',
        'remain_count', 'status', 'start_time', 'end_time'
    ];

    public function codeBox()
    {
        return $this->hasOne(CodeBox::class, 'id', 'code_box_id');
    }

    public function codeBatch()
    {
        return $this->hasOne(CodeBatch::class, 'id', 'code_batch_id');
    }
}