<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placeholder_mappings', function (Blueprint $table) {
            $table->id();

            // FK apenas para template_placeholders
            $table->foreignId('placeholder_id')
                  ->constrained('template_placeholders')
                  ->cascadeOnDelete();

            // Armazena o ID da oportunidade, mas sem constraint de FK
            $table->unsignedBigInteger('opportunity_id');

            $table->enum('source_type', ['meta','registration','agent']);
            $table->string('source_key', 100);
            $table->unsignedTinyInteger('priority')->default(1);

            $table->unique(
                ['placeholder_id', 'opportunity_id', 'priority'],
                'mapping_unique'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placeholder_mappings');
    }
};
