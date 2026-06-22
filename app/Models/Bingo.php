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
        'card_quantity', 'numbers_per_card', 'round_quantity', 'cards_per_page',
        'card_title', 'card_logo_path', 'only_linked_cards',
        'cards_pdf_path', 'cards_pdf_status', 'cards_pdf_generated_at',
        'status', 'current_prize_pattern_id', 'created_by'
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime:H:i',
        'only_linked_cards' => 'boolean',
        'cards_pdf_generated_at' => 'datetime',
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

    public function rounds()
    {
        return $this->hasMany(BingoRound::class)->orderBy('round_number');
    }

    public function activeRound()
    {
        return $this->hasOne(BingoRound::class)->where('status', 'ongoing');
    }

    public function currentRound()
    {
        return $this->activeRound()->first()
            ?: $this->rounds()->where('status', 'pending')->orderBy('round_number')->first()
            ?: $this->rounds()->orderByDesc('round_number')->first();
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

    public function getCardsPdfBadgeAttribute()
    {
        return match($this->cards_pdf_status) {
            'ready' => '<span class="badge badge-success">PDF Disponível</span>',
            'processing' => '<span class="badge badge-info">Preparando PDF</span>',
            'failed' => '<span class="badge badge-danger">PDF com Erro</span>',
            default => '<span class="badge badge-secondary">Gerar PDF</span>',
        };
    }
}
