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
            $table->string('currency', 3)->default('BRL');
            $table->decimal('hourly_rate', 10, 2)->default(0);

            // Realign the earlier en/UTC defaults with the personalization spec.
            $table->string('locale', 10)->default('pt-BR')->change();
            $table->string('timezone', 64)->default('America/Sao_Paulo')->change();
        });

        // Backfill existing rows onto the new defaults.
        DB::table('users')->update([
            'locale' => 'pt-BR',
            'timezone' => 'America/Sao_Paulo',
            'currency' => 'BRL',
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'hourly_rate']);
            $table->string('locale', 10)->default('en')->change();
            $table->string('timezone', 64)->default('UTC')->change();
        });
    }
};
