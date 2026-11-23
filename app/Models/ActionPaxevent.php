<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Action réalisée par PaxEvent au nom d'un organisateur (mode « Gérer »).
class ActionPaxevent extends Model
{
    protected $table = 'actions_paxevent';

    protected $fillable = ['user_id', 'admin_id', 'admin_email', 'methode', 'route', 'resume', 'ip'];

    public function organisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
