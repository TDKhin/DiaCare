<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('glucose_target_min')->nullable()->after('email');
            $table->unsignedSmallInteger('glucose_target_max')->nullable()->after('glucose_target_min');
            $table->decimal('height_cm', 5, 2)->nullable()->after('glucose_target_max');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('height_cm');
            $table->string('emergency_contact_name')->nullable()->after('weight_kg');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('doctor_name')->nullable()->after('emergency_contact_phone');
            $table->text('medical_notes')->nullable()->after('doctor_name');
            $table->text('health_goals')->nullable()->after('medical_notes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'glucose_target_min',
                'glucose_target_max',
                'height_cm',
                'weight_kg',
                'emergency_contact_name',
                'emergency_contact_phone',
                'doctor_name',
                'medical_notes',
                'health_goals',
            ]);
        });
    }
};
