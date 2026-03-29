<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniFlushPreset extends Model
{
    protected $table = 'miniflush_presets';
    protected $fillable = [
        'name',
        'min_bet',
        'max_bet',
        'burn_card',
        'hl_min',
        'hl_max',
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
