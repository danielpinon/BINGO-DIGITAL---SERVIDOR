<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id');
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('prize_pattern_id');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->timestamp('confirmed_at');
            $table->unsignedBigInteger('confirmed_by');
            $table->timestamps();
            
            $table->foreign('bingo_id')->references('id')->on('bingos')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards');
            $table->foreign('prize_pattern_id')->references('id')->on('bingo_prize_patterns');
            $table->foreign('responsible_id')->references('id')->on('responsibles')->onDelete('set null');
            $table->foreign('confirmed_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
