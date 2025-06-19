<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_settings', function (Blueprint $table) {
            $table->id();
            // FK opcional para ExternalOpportunity
            $table->unsignedBigInteger('opportunity_id')->unique();
            $table->enum('category', ['execucao', 'premiacao', 'compromisso'])->default('execucao');
            $table->unsignedInteger('start_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_settings');
    }
};
