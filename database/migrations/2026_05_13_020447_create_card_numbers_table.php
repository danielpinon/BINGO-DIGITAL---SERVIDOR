<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->tinyInteger('row');
            $table->tinyInteger('col');
            $table->integer('number');
            $table->timestamps();
            
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_numbers');
    }
};
