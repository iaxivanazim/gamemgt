<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameTablePayoutRule extends Model
{
    protected $fillable = ['table_id', 'payout_id', 'is_active'];

    public function table()
    {
        return $this->belongsTo(GameTable::class);
    }

    public function payoutRule()
    {
        return $this->belongsTo(PayoutRule::class, 'payout_id', 'payout_id');
    }
}
