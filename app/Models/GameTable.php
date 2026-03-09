<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameTable extends Model
{
    protected $fillable = [
        'table_name',
        'game_type_id',
        'active_mac',
        'float',
        'status',
        'felt_color',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }

    // public function theme()
    // {
    //     return $this->belongsTo(Theme::class);
    // }

    public function config()
    {
        return $this->hasOne(GameTableConfig::class, 'table_id')->with('preset');
    }

    public function payoutRules()
    {
        return $this->hasMany(GameTablePayoutRule::class, 'table_id');
    }

    public function activePayoutRules()
    {
        return $this->hasMany(GameTablePayoutRule::class, 'table_id')
            ->where('is_active', 1)
            ->with('payoutRule');
    }
}
