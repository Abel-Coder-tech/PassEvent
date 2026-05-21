<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'email',
        'actif',
        'desabonne_le',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'desabonne_le' => 'datetime',
        ];
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
