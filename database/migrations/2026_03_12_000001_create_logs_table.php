<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('location')->nullable();
            $table->string('from_location')->nullable();
            $table->dateTime('traveled_to_date'); // in-universe destination date
            $table->timestamp('departure_timestamp'); // real-world time of travel
            $table->index(['user_id', 'departure_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
