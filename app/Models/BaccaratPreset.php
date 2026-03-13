<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaccaratPreset extends Model
{
    protected $fillable = [
        'name',
        'min_bet',
        'max_bet',
        'side_min_bet',
        'side_max_bet',
        'commission',
        // 'enable_pairbets',
        // 'enable_lucky6',
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
