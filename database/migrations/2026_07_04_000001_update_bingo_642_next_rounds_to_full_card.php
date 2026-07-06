<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $secondPatternId = DB::table('bingo_prize_patterns')
            ->where('bingo_id', 642)
            ->where('pattern_order', 2)
            ->value('id');

        DB::table('bingo_prize_patterns')
            ->where('bingo_id', 642)
            ->where('pattern_order', 2)
            ->update([
                'pattern_type' => 'full_card',
                'updated_at' => $now,
            ]);

        DB::table('bingo_prize_patterns')
            ->where('bingo_id', 642)
            ->where('pattern_order', 3)
            ->update([
                'pattern_type' => 'full_card',
                'updated_at' => $now,
            ]);

        $hasSecondRoundWinner = DB::table('winners')
            ->where('bingo_id', 642)
            ->whereIn('bingo_round_id', function ($query) {
                $query->select('id')
                    ->from('bingo_rounds')
                    ->where('bingo_id', 642)
                    ->where('round_number', 2);
            })
            ->exists();

        if ($secondPatternId && !$hasSecondRoundWinner) {
            DB::table('bingo_rounds')
                ->where('bingo_id', 642)
                ->where('round_number', 1)
                ->update([
                    'status' => 'finished',
                    'current_prize_pattern_id' => null,
                    'updated_at' => $now,
                ]);

            DB::table('bingo_rounds')
                ->where('bingo_id', 642)
                ->where('round_number', 2)
                ->update([
                    'status' => 'ongoing',
                    'current_prize_pattern_id' => $secondPatternId,
                    'finished_at' => null,
                    'updated_at' => $now,
                ]);

            DB::table('bingo_rounds')
                ->where('bingo_id', 642)
                ->where('round_number', 3)
                ->update([
                    'status' => 'pending',
                    'updated_at' => $now,
                ]);

            DB::table('bingos')
                ->where('id', 642)
                ->update([
                    'status' => 'ongoing',
                    'current_prize_pattern_id' => $secondPatternId,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        $now = now();

        DB::table('bingo_prize_patterns')
            ->where('bingo_id', 642)
            ->where('pattern_order', 2)
            ->update([
                'pattern_type' => 'quina',
                'updated_at' => $now,
            ]);

        DB::table('bingo_prize_patterns')
            ->where('bingo_id', 642)
            ->where('pattern_order', 3)
            ->update([
                'pattern_type' => 'quina',
                'updated_at' => $now,
            ]);
    }
};
