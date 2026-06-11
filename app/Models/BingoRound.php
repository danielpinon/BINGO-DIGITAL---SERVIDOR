<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BingoRound extends Model
{
    protected $fillable = [
        'bingo_id',
        'round_number',
        'status',
        'current_prize_pattern_id',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }

    public function currentPrizePattern()
    {
        return $this->belongsTo(BingoPrizePattern::class, 'current_prize_pattern_id');
    }

    public function drawnNumbers()
    {
        return $this->hasMany(DrawnNumber::class)->orderBy('drawn_at');
    }

    public function winners()
    {
        return $this->hasMany(Winner::class);
    }
}
