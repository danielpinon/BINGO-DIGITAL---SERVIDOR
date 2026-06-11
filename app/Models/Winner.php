<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    protected $fillable = [
        'bingo_id', 'bingo_round_id', 'card_id', 'prize_pattern_id',
        'responsible_id', 'confirmed_at', 'confirmed_by'
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }

    public function round()
    {
        return $this->belongsTo(BingoRound::class, 'bingo_round_id');
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function prizePattern()
    {
        return $this->belongsTo(BingoPrizePattern::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Responsible::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
