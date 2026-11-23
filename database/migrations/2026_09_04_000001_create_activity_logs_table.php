<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity');
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('calories_burned')->default(0);
            $table->dateTime('logged_at');
            $table->timestamps();
            $table->index(['user_id', 'logged_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('activity_logs'); }
};
