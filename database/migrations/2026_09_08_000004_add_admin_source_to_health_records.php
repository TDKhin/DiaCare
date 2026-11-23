<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->boolean('created_by_admin')->default(false)->after('user_id')->index();
        });

        Schema::table('medications', function (Blueprint $table): void {
            $table->boolean('created_by_admin')->default(false)->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropColumn('created_by_admin');
        });

        Schema::table('medications', function (Blueprint $table): void {
            $table->dropColumn('created_by_admin');
        });
    }
};
