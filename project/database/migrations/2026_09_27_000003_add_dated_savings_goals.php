<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('savings_goal_due_date')->nullable();
            $table->date('savings_goal_started_at')->nullable();
            $table->unsignedInteger('savings_goal_revision')->default(0);
        });

        Schema::create('savings_goal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('goal_revision');
            $table->string('notification_type', 16);
            $table->decimal('goal_amount', 10, 2);
            $table->decimal('saved_amount', 12, 2);
            $table->date('goal_due_date');
            $table->timestamp('sent_at');
            $table->unique(['user_id', 'goal_revision', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goal_notifications');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'savings_goal_due_date',
                'savings_goal_started_at',
                'savings_goal_revision',
            ]);
        });
    }
};
