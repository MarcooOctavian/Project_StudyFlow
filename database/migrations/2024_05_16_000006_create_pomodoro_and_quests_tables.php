<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pomodoro_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
        });

        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['daily', 'weekly', 'milestone']);
            $table->integer('goal_value');
            $table->integer('points_reward')->default(0);
            $table->timestamps();
        });

        Schema::create('user_quest_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->integer('current_value')->default(0);
            $table->boolean('completed')->default(false);
            $table->date('reset_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quest_progress');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('pomodoro_sessions');
    }
};
