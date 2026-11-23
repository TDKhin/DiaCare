<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'emergency_contact_name',
        'emergency_contact_phone',
        'doctor_name',
        'medical_notes',
        'health_goals',
    ];

    public function up(): void
    {
        $existing = array_values(array_filter($this->columns, fn (string $column) => Schema::hasColumn('users', $column)));
        if ($existing) {
            Schema::table('users', function (Blueprint $table) use ($existing): void {
                $table->dropColumn($existing);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('doctor_name')->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('health_goals')->nullable();
        });
    }
};
