<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bingos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('event_time');
            $table->integer('number_range_start')->default(1);
            $table->integer('number_range_end')->default(75);
            $table->integer('card_quantity')->default(0);
            $table->integer('numbers_per_card')->default(25);
            $table->enum('status', ['preparation', 'ongoing', 'finished'])->default('preparation');
            $table->unsignedBigInteger('current_prize_pattern_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bingos');
    }
};
