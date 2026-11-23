<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'activity', 'intensity', 'duration_minutes', 'calories_burned', 'logged_at', 'pre_glucose_reading_id', 'post_glucose_reading_id', 'created_by_admin'];
    protected function casts(): array { return ['logged_at' => 'datetime', 'created_by_admin' => 'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function preGlucoseReading(): BelongsTo { return $this->belongsTo(GlucoseReading::class, 'pre_glucose_reading_id'); }
    public function postGlucoseReading(): BelongsTo { return $this->belongsTo(GlucoseReading::class, 'post_glucose_reading_id'); }
}
