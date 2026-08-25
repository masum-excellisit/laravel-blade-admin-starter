<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type', 20)->index();
            $table->json('parts')->nullable();
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->text('error')->nullable();
            $table->string('source', 20)->default('manual');
            $table->boolean('is_protected')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
