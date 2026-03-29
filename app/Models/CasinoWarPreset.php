<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasinoWarPreset extends Model
{
    protected $table = 'casinowar_presets';
    protected $fillable = [
        'name',
        'min_bet',
        'max_bet',
        'burn_card',
        'tie_min',
        'tie_max',
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
