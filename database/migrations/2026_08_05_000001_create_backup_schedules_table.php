<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->default('full');
            $table->string('frequency', 20)->default('daily');
            $table->string('time', 5)->default('02:00');
            $table->unsignedTinyInteger('day_of_week')->default(0);
            $table->unsignedTinyInteger('day_of_month')->default(1);
            $table->unsignedSmallInteger('retention')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        Schema::table('backups', function (Blueprint $table) {
            $table->foreignId('backup_schedule_id')->nullable()->after('source')
                ->constrained('backup_schedules')->nullOnDelete();
        });

        // Superseded by the backup_schedules table.
        DB::table('settings')->whereIn('key', [
            'backup_schedule_enabled', 'backup_schedule_frequency', 'backup_schedule_time',
            'backup_schedule_day', 'backup_schedule_type',
        ])->delete();
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('backup_schedule_id');
        });

        Schema::dropIfExists('backup_schedules');
    }
};
