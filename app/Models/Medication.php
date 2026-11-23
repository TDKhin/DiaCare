<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medication extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'name', 'dose', 'instructions', 'scheduled_at', 'active', 'created_by_admin'];
    protected function casts(): array { return ['scheduled_at' => 'datetime', 'active' => 'boolean', 'created_by_admin' => 'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
