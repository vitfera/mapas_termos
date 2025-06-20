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

            $table->string('placeholder_key');
            $table->string('placeholder_label');

            $table->unsignedBigInteger('opportunity_id');

            $table->unsignedBigInteger('field_id');
            $table->unsignedTinyInteger('priority')->default(1);

            $table->unique(
                ['placeholder_key', 'opportunity_id', 'priority'],
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
