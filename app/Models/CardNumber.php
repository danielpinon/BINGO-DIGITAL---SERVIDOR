<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardNumber extends Model
{
    protected $fillable = ['card_id', 'row', 'col', 'number'];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
