<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BingoStatusLog extends Model
{
    protected $fillable = ['bingo_id', 'from_status', 'to_status', 'changed_by'];

    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
