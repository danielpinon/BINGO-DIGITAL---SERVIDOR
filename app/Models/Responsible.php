<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsible extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'status'];

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function winners()
    {
        return $this->hasMany(Winner::class);
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active'
            ? '<span class="badge badge-success">Ativo</span>'
            : '<span class="badge badge-danger">Inativo</span>';
    }
}
