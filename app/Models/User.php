<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'ctm_agent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function qaLogs(): HasMany
    {
        return $this->hasMany(QaLog::class, 'analyst_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isQA(): bool
    {
        return $this->role === 'qa';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }
}
