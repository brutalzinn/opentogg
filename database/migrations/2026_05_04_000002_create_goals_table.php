<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vector_id')->nullable()->constrained()->nullOnDelete();
            $table->string('operator'); // gte, lte
            $table->decimal('target_hours', 8, 2);
            $table->string('period'); // daily, weekly, monthly
            $table->string('webhook_url')->nullable();
            $table->timestamp('last_achieved_at')->nullable();
            $table->timestamp('createAt')->nullable();
            $table->timestamp('updateAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
