<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carte extends Model
{
    protected $fillable = [
        'carte_name', 'pid', 'status'
    ];

    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id')
            ->select('id', 'carte_name', 'pid')
            ->with(['children']);
    }

    public static function GetAllMenuTree($pid=0, &$arr)
    {
        $arr = self::query()
            ->select('id','carte_name','pid')
            ->where('pid',$pid)
            ->with(['children'])
            ->get()->toArray();

        return $arr;
    }
}