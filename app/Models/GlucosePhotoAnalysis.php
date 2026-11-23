<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlucosePhotoAnalysis extends Model
{
    protected $fillable = [
        'user_id', 'photo_path', 'status', 'value', 'unit', 'confidence', 'analysis', 'error_message',
    ];

    protected $casts = [
        'analysis' => 'array',
        'confidence' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
