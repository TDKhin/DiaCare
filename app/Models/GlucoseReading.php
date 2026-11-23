<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlucoseReading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'value', 'context', 'recorded_at', 'notes'];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preExerciseFor(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'pre_glucose_reading_id');
    }

    public function postExerciseFor(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'post_glucose_reading_id');
    }
}
