<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->string('tip_title');
            $table->text('tip_body');
            $table->decimal('potential_savings', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'dismissed', 'pinned'])->default('active');
            $table->boolean('is_bookmarked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_tips');
    }
};
