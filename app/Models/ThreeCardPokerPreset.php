<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreeCardPokerPreset extends Model
{
    protected $fillable = [
        'name',
        'min_bet',
        'max_bet',
        'side_min',
        'side_max',
        'six_card_bonus',
        'chip_preset_id',
        'status'
    ];

    public function chipPreset()
    {
        return $this->belongsTo(Chip::class, 'chip_preset_id');
    }
    public function tableAssignment()
    {
        return $this->morphOne(GameTableConfig::class, 'preset');
    }
}
