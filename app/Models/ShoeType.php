<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoeType extends Model
{
    protected $fillable = [
        'shoe_name',
    ];

    public function gameTables()
    {
        return $this->hasMany(GameTable::class);
    }
}
