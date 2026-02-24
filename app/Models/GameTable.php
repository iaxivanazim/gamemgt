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
        'theme_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }
}
