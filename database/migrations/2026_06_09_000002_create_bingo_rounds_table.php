<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bingo_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id');
            $table->unsignedTinyInteger('round_number');
            $table->enum('status', ['pending', 'ongoing', 'finished'])->default('pending');
            $table->unsignedBigInteger('current_prize_pattern_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->foreign('bingo_id')->references('id')->on('bingos')->onDelete('cascade');
            $table->foreign('current_prize_pattern_id')->references('id')->on('bingo_prize_patterns')->nullOnDelete();
            $table->unique(['bingo_id', 'round_number']);
        });

        $now = now();
        DB::table('bingos')->orderBy('id')->each(function ($bingo) use ($now) {
            DB::table('bingo_rounds')->insert([
                'bingo_id' => $bingo->id,
                'round_number' => 1,
                'status' => $bingo->status === 'finished' ? 'finished' : ($bingo->status === 'ongoing' ? 'ongoing' : 'pending'),
                'current_prize_pattern_id' => $bingo->current_prize_pattern_id,
                'started_at' => $bingo->status === 'preparation' ? null : $now,
                'finished_at' => $bingo->status === 'finished' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bingo_rounds');
    }
};
