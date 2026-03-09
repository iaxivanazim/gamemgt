<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackjackPreset extends Model
{
    protected $fillable = [
        'name',
        'min_bet',
        'max_bet',
        'pair_min',
        'pair_max',
        'split_type',
        'rule_type',
        'enable_777_charlie',
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
