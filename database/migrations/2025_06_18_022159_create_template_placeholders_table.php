<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_placeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')
                  ->constrained('templates')
                  ->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('label', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_placeholders');
    }
};
