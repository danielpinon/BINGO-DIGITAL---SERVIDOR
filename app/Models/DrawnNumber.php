<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawnNumber extends Model
{
    protected $fillable = ['bingo_id', 'number', 'drawn_at'];

    protected $casts = [
        'drawn_at' => 'datetime',
    ];

    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }
}
