<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drawn_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id');
            $table->integer('number');
            $table->timestamp('drawn_at');
            $table->timestamps();
            
            $table->foreign('bingo_id')->references('id')->on('bingos')->onDelete('cascade');
            $table->unique(['bingo_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drawn_numbers');
    }
};
