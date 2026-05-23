<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DragontigerHistory extends Model
{
    public $timestamps = false;
    protected $table = 'dragontiger_history';

    protected $fillable = [
        'shoe_no', 'table_id', 'game_no', 'tab_id', 'dragon_card', 'tiger_card',
        'winner', 'side_win', 'bet_position', 'bet_amount',
        'win_amount', 'current_credit', 'date_time',
    ];

    protected $casts = [
        'dragon_card' => 'array',
        'tiger_card' => 'array',
        'bet_position' => 'array',
        'side_win'     => 'array',
        'bet_amount'   => 'float',
        'win_amount'   => 'float',
        'current_credit' => 'float',
        'date_time'    => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(GameTable::class, 'table_id');
    }
}
