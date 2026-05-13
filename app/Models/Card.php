<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'bingo_id', 'responsible_id', 'card_number', 'status'
    ];

    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Responsible::class);
    }

    public function numbers()
    {
        return $this->hasMany(CardNumber::class)->orderBy('row')->orderBy('col');
    }

    public function winner()
    {
        return $this->hasOne(Winner::class);
    }

    public function getGridAttribute()
    {
        $grid = [];
        foreach ($this->numbers as $num) {
            $grid[$num->row][$num->col] = $num->number;
        }
        return $grid;
    }
}
