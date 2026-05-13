<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bingo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'event_date', 'event_time',
        'number_range_start', 'number_range_end',
        'card_quantity', 'numbers_per_card',
        'status', 'current_prize_pattern_id', 'created_by'
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime:H:i',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prizePatterns()
    {
        return $this->hasMany(BingoPrizePattern::class)->orderBy('pattern_order');
    }

    public function currentPrizePattern()
    {
        return $this->belongsTo(BingoPrizePattern::class, 'current_prize_pattern_id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function drawnNumbers()
    {
        return $this->hasMany(DrawnNumber::class)->orderBy('drawn_at');
    }

    public function winners()
    {
        return $this->hasMany(Winner::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(BingoStatusLog::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'preparation' => '<span class="badge badge-warning">Em Preparação</span>',
            'ongoing' => '<span class="badge badge-success">Em Andamento</span>',
            'finished' => '<span class="badge badge-info">Finalizado</span>',
            default => '<span class="badge badge-secondary">' . $this->status . '</span>',
        };
    }
}
