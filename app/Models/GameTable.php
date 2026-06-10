<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GameTable extends Model
{
    protected $fillable = [
        'table_name',
        'game_type_id',
        'active_mac',
        'float',
        'bet_index',
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

    public function currentFloat()
    {
        return $this->hasOne(TableFloat::class, 'table_id')
            ->whereNull('closed_at');
    }

    public function isFloatOpen(): bool
    {
        return !is_null($this->currentFloat);
    }

    public function getLiveFloatAttribute(): ?float
    {
        $openSession = $this->currentFloat;
        if (!$openSession) return null;

        // Get latest float_balance from ledger for this table + gameday
        return DB::table('table_ledgers')
            ->where('table_id', $this->id)
            ->where('gameday', $openSession->gameday)
            ->orderBy('txn_id', 'desc')
            ->value('float_balance');
    }

    public function getActiveBetRangeAttribute(): array
    {
        $preset = $this->config?->preset;
        if (!$preset) return [];

        $index = ($this->bet_index ?? 1) - 1; // convert to 0-based

        $mins = explode('|', $preset->min_bet);
        $maxs = explode('|', $preset->max_bet);

        return [
            'min' => isset($mins[$index]) ? (float)$mins[$index] : (float)$mins[0],
            'max' => isset($maxs[$index]) ? (float)$maxs[$index] : (float)$maxs[0],
        ];
    }
}
