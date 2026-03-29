<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vector_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('createAt')->nullable();
            $table->timestamp('updateAt')->nullable();
        });

        Schema::create('time_entry_tag', function (Blueprint $table) {
            $table->foreignId('time_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['time_entry_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entry_tag');
        Schema::dropIfExists('time_entries');
    }
};
