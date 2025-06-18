<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('generated_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('opportunity_id');
            $table->unsignedBigInteger('registration_id');
            $table->string('filename');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('generated_terms');
    }
};
