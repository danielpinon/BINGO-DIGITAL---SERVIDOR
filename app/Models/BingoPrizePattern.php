<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BingoPrizePattern extends Model
{
    protected $fillable = [
        'bingo_id', 'name', 'pattern_type', 'pattern_order', 'is_completed'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }

    public function winners()
    {
        return $this->hasMany(Winner::class, 'prize_pattern_id');
    }
}
