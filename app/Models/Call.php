<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ctm_call_id',
        'ctm_sid',
        'tracking_number',
        'tracking_label',
        'caller_number',
        'caller_city',
        'caller_state',
        'direction',
        'source',
        'agent_name',
        'agent_id',
        'recording_url',
        'local_recording_path',
        'transferred',
        'transcript_url',
        'transcript_text',
        'transcript_json',
        'transcript_id',
        'status',
        'call_datetime',
        'duration',
        'talk_time',
    ];

    protected function casts(): array
    {
        return [
            'transcript_json' => 'array',
            'call_datetime' => 'datetime',
            'duration' => 'integer',
            'talk_time' => 'integer',
            'transferred' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function qaLog(): HasOne
    {
        return $this->hasOne(QaLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAnalyzed($query)
    {
        return $query->where('status', 'analyzed');
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('call_datetime', [$start, $end]);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('caller_number', 'like', "%{$term}%")
                ->orWhere('agent_name', 'like', "%{$term}%")
                ->orWhere('tracking_number', 'like', "%{$term}%");
        });
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
