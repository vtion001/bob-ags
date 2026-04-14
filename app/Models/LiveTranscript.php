<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveTranscript extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_monitoring_id',
        'text',
        'speaker',
        'confidence',
        'start_time',
        'end_time',
        'is_final',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'start_time' => 'float',
            'end_time' => 'float',
            'is_final' => 'boolean',
        ];
    }

    public function liveMonitoring(): BelongsTo
    {
        return $this->belongsTo(LiveMonitoring::class);
    }
}
