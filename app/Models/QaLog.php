<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QaLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'analyst_id',
        'total_score',
        'ztp_failed',
        'sentiment',
        'disposition',
        'criteria_scores',
        'rubric_breakdown',
        'ztp_violations',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'criteria_scores' => 'array',
            'rubric_breakdown' => 'array',
            'ztp_violations' => 'array',
            'total_score' => 'decimal:2',
            'ztp_failed' => 'boolean',
        ];
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function scopeFailed($query)
    {
        return $query->where('ztp_failed', true);
    }

    public function scopeByScore($query, $min, $max = 100)
    {
        return $query->whereBetween('total_score', [$min, $max]);
    }

    public function getScoreGradeAttribute(): string
    {
        if ($this->total_score >= 85) return 'Excellent';
        if ($this->total_score >= 70) return 'Good';
        if ($this->total_score >= 50) return 'Needs Improvement';
        return 'Poor';
    }
}
