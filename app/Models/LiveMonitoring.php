<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LiveMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ctm_call_id',
        'user_id',
        'agent_name',
        'caller_number',
        'status',
        'transcript_text',
        'current_context',
        'active_suggestions',
        'ztp_alerts',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'current_context' => 'array',
            'active_suggestions' => 'array',
            'ztp_alerts' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transcripts(): HasMany
    {
        return $this->hasMany(LiveTranscript::class)->orderBy('start_time');
    }

    public static function generateSessionId(): string
    {
        return 'lm_' . Str::uuid();
    }

    public function appendTranscript(string $text, string $speaker = 'unknown', ?float $startTime = null, ?float $endTime = null): LiveTranscript
    {
        $this->transcript_text = ($this->transcript_text ?? '') . ' ' . $text;

        $transcript = $this->transcripts()->create([
            'text' => $text,
            'speaker' => $speaker,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $this->save();

        return $transcript;
    }

    public function addZtpAlert(array $alert): void
    {
        $alerts = $this->ztp_alerts ?? [];
        $alerts[] = array_merge($alert, ['timestamp' => now()->toIso8601String()]);
        $this->ztp_alerts = $alerts;
        $this->save();
    }

    public function updateSuggestions(array $suggestions): void
    {
        $this->active_suggestions = $suggestions;
        $this->save();
    }

    public function updateContext(array $context): void
    {
        $this->current_context = array_merge($this->current_context ?? [], $context);
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getTranscriptPreview(int $length = 100): string
    {
        return Str::limit($this->transcript_text ?? '', $length);
    }
}
