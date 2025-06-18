<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSourceKeyFromPlaceholderMappingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('placeholder_mappings', function (Blueprint $table) {
            $table->dropColumn('source_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placeholder_mappings', function (Blueprint $table) {
            // Caso precise reverter, recria a coluna como string
            $table->string('source_key')->after('source_type');
        });
    }
}
