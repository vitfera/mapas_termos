<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermGenerationProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'template_id',
        'user_id',
        'status',
        'total_registrations',
        'processed_count',
        'zip_filename',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(ExternalOpportunity::class, 'opportunity_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_registrations === 0) {
            return 0;
        }
        
        return round(($this->processed_count / $this->total_registrations) * 100, 2);
    }

    public function getIsCompletedAttribute(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(string $zipFilename = null): void
    {
        $this->update([
            'status' => 'completed',
            'zip_filename' => $zipFilename,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    public function incrementProcessed(): void
    {
        $this->increment('processed_count');
    }
}
