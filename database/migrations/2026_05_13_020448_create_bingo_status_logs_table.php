<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bingo_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id');
            $table->string('from_status');
            $table->string('to_status');
            $table->unsignedBigInteger('changed_by');
            $table->timestamps();
            
            $table->foreign('bingo_id')->references('id')->on('bingos')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bingo_status_logs');
    }
};
