<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerCarte extends Model
{
    public function carte()
    {
        return $this->hasOne(Carte::class, 'id', 'carte_id');
    }
}