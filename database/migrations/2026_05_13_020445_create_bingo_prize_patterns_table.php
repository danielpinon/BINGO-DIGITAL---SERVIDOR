<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bingo_prize_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id');
            $table->string('name');
            $table->enum('pattern_type', ['line', 'quina', 'full_card', 'cross', 'corners']);
            $table->integer('pattern_order')->default(1);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            
            $table->foreign('bingo_id')->references('id')->on('bingos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bingo_prize_patterns');
    }
};
