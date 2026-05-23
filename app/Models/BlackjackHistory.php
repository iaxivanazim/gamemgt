<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackjackHistory extends Model
{
    public $timestamps = false;
    protected $table = 'blackjack_history';

    protected $fillable = [
        'table_id', 'game_no', 'tab_id', 'player_cards', 'dealer_cards', 'split_hands', 'double_amount','insurance_amount',
        'winner', 'side_win', 'bet_position', 'bet_amount',
        'win_amount', 'current_credit', 'date_time', 
    ];

    protected $casts = [
        'player_cards' => 'array',
        'dealer_cards' => 'array',
        'split_hands'    => 'array',
        'bet_position' => 'array',
        'side_win'     => 'array',
        'double_amount' => 'float',
        'insurance_amount' => 'float',
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
