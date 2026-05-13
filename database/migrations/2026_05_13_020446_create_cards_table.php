<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->string('card_number');
            $table->enum('status', ['available', 'distributed', 'returned'])->default('available');
            $table->timestamps();
            
            $table->foreign('bingo_id')->references('id')->on('bingos')->onDelete('cascade');
            $table->foreign('responsible_id')->references('id')->on('responsibles')->nullOnDelete();
            $table->unique(['bingo_id', 'card_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
