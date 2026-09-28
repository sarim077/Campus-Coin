<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->softDeletes();
            $table->date('next_occurrence_at')->nullable();
            $table->foreignId('recurrence_parent_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->unique(['recurrence_parent_id', 'date']);
        });

        Schema::table('saving_tips', function (Blueprint $table) {
            $table->text('user_note')->nullable();
        });

        Schema::table('insights', function (Blueprint $table) {
            $table->text('user_note')->nullable();
        });

        Schema::create('saving_tip_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->decimal('potential_savings', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('saving_tips', function (Blueprint $table) {
            $table->foreignId('source_template_id')->nullable()->constrained('saving_tip_templates')->nullOnDelete();
        });

        Schema::create('category_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->string('normalized_description', 255);
            $table->string('description', 255);
            $table->timestamps();
            $table->unique(['user_id', 'type', 'normalized_description']);
        });

        DB::table('transactions')
            ->where('is_recurring', true)
            ->whereNull('next_occurrence_at')
            ->orderBy('id')
            ->get(['id', 'date', 'recurrence_frequency'])
            ->each(function (object $transaction): void {
                $date = \Carbon\Carbon::parse($transaction->date);
                $next = $transaction->recurrence_frequency === 'weekly'
                    ? $date->addWeek()
                    : $date->addMonthNoOverflow();

                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['next_occurrence_at' => $next->toDateString()]);
            });
    }

    public function down(): void
    {
        Schema::table('saving_tips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_template_id');
        });

        Schema::dropIfExists('saving_tip_templates');
        Schema::dropIfExists('category_corrections');

        Schema::table('insights', function (Blueprint $table) {
            $table->dropColumn('user_note');
        });

        Schema::table('saving_tips', function (Blueprint $table) {
            $table->dropColumn('user_note');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['recurrence_parent_id', 'date']);
            $table->dropConstrainedForeignId('recurrence_parent_id');
            $table->dropColumn(['deleted_at', 'next_occurrence_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });
    }
};
