<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) adiciona last_sequence (inicialmente nulo) em opportunity_settings
        Schema::table('opportunity_settings', function (Blueprint $table) {
            $table->unsignedInteger('last_sequence')
                  ->nullable()
                  ->after('start_number');
        });

        // 2) inicializa last_sequence = start_number - 1 para todas as linhas já existentes
        DB::table('opportunity_settings')->update([
            'last_sequence' => DB::raw('start_number - 1'),
        ]);

        // 3) garante que last_sequence não seja nulo a partir de agora
        Schema::table('opportunity_settings', function (Blueprint $table) {
            $table->unsignedInteger('last_sequence')
                  ->nullable(false)
                  ->change();
        });

        // 4) adiciona sequence_number (nullable) em generated_terms
        Schema::table('generated_terms', function (Blueprint $table) {
            $table->unsignedInteger('sequence_number')
                  ->nullable()
                  ->after('registration_id');
        });
    }

    public function down(): void
    {
        Schema::table('generated_terms', function (Blueprint $table) {
            $table->dropColumn('sequence_number');
        });

        Schema::table('opportunity_settings', function (Blueprint $table) {
            $table->dropColumn('last_sequence');
        });
    }
};
