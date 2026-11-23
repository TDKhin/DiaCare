<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'photo_path', 'status', 'analysis', 'error_message'];

    protected function casts(): array
    {
        return ['analysis' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
