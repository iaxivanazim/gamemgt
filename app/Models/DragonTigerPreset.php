<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DragonTigerPreset extends Model
{
    protected $table = 'dragontiger_presets';
    protected $fillable = [
        'name',
        'min_bet',
        'max_bet',
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
