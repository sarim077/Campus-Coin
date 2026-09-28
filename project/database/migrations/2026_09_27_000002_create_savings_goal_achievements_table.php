<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goal_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('goal_month', 7);
            $table->decimal('goal_amount', 10, 2);
            $table->decimal('balance', 12, 2);
            $table->timestamp('notified_at');
            $table->unique(['user_id', 'goal_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goal_achievements');
    }
};
