<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ctm_agent_id',
        'ctm_agent_email',
        'ctm_agent_name',
        'user_group',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'agent_id', 'ctm_agent_id');
    }

    public function isLinked(): bool
    {
        return $this->user_id !== null;
    }

    public function getCallCount(): int
    {
        return $this->calls()->count();
    }
}
