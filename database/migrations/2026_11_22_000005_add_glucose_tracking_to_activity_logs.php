<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->string('intensity', 20)->nullable()->after('activity');
            $table->foreignId('pre_glucose_reading_id')->nullable()->after('user_id')->constrained('glucose_readings')->nullOnDelete();
            $table->foreignId('post_glucose_reading_id')->nullable()->after('pre_glucose_reading_id')->constrained('glucose_readings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('post_glucose_reading_id');
            $table->dropConstrainedForeignId('pre_glucose_reading_id');
            $table->dropColumn('intensity');
        });
    }
};
