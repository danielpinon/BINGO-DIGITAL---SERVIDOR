<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class Bingo642Seeder extends Seeder
{
    private const BINGO_ID = 642;
    private const DATA_FILE = 'data/bingo_642_cards.json.gz';

    public function run(): void
    {
        DB::disableQueryLog();

        $cards = $this->loadCards();
        $now = now();
        $user = User::firstOrCreate(
            ['email' => 'admin@material.com'],
            [
                'name' => 'Admin Admin',
                'password' => Hash::make('secret'),
                'email_verified_at' => $now,
            ]
        );

        DB::transaction(function () use ($cards, $now, $user) {
            $this->clearExistingBingo();

            DB::table('bingos')->insert([
                'id' => self::BINGO_ID,
                'name' => 'Bingo 7000 Cartelas',
                'description' => 'Bingo gerado com 7000 cartelas para impressão em PDF.',
                'event_date' => '2026-07-04',
                'event_time' => '10:00:00',
                'number_range_start' => 1,
                'number_range_end' => 75,
                'card_quantity' => 7000,
                'card_generation_status' => 'ready',
                'card_generation_message' => 'Cartelas geradas pela seed Bingo642Seeder.',
                'card_generation_started_at' => $now,
                'card_generation_completed_at' => $now,
                'numbers_per_card' => 25,
                'round_quantity' => 3,
                'cards_per_page' => 1,
                'card_title' => 'BINGO',
                'card_logo_path' => null,
                'card_template_path' => null,
                'only_linked_cards' => false,
                'cards_pdf_path' => null,
                'cards_pdf_status' => 'pending',
                'cards_pdf_generated_at' => null,
                'status' => 'preparation',
                'current_prize_pattern_id' => null,
                'created_by' => $user->id,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            $patternId = DB::table('bingo_prize_patterns')->insertGetId([
                'bingo_id' => self::BINGO_ID,
                'name' => 'Cartela Cheia',
                'pattern_type' => 'full_card',
                'pattern_order' => 1,
                'is_completed' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $rounds = [];
            for ($round = 1; $round <= 3; $round++) {
                $rounds[] = [
                    'bingo_id' => self::BINGO_ID,
                    'round_number' => $round,
                    'status' => 'pending',
                    'current_prize_pattern_id' => $patternId,
                    'started_at' => null,
                    'finished_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('bingo_rounds')->insert($rounds);

            foreach (array_chunk($cards, 500) as $chunk) {
                DB::table('cards')->insert(array_map(fn (array $card) => [
                    'bingo_id' => self::BINGO_ID,
                    'responsible_id' => null,
                    'card_number' => $card['card_number'],
                    'status' => 'available',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));
            }

            $cardIds = DB::table('cards')
                ->where('bingo_id', self::BINGO_ID)
                ->pluck('id', 'card_number');

            $numberRows = [];
            foreach ($cards as $card) {
                $cardId = $cardIds[$card['card_number']] ?? null;

                if (!$cardId) {
                    throw new RuntimeException("Cartela {$card['card_number']} nao foi criada.");
                }

                foreach ($card['numbers'] as $index => $number) {
                    $numberRows[] = [
                        'card_id' => $cardId,
                        'row' => intdiv($index, 5),
                        'col' => $index % 5,
                        'number' => $number,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($numberRows) >= 2000) {
                        DB::table('card_numbers')->insert($numberRows);
                        $numberRows = [];
                    }
                }
            }

            if ($numberRows) {
                DB::table('card_numbers')->insert($numberRows);
            }
        });
    }

    private function clearExistingBingo(): void
    {
        $cardIds = DB::table('cards')
            ->where('bingo_id', self::BINGO_ID)
            ->pluck('id');

        foreach ($cardIds->chunk(1000) as $chunk) {
            DB::table('card_numbers')->whereIn('card_id', $chunk)->delete();
        }

        DB::table('winners')->where('bingo_id', self::BINGO_ID)->delete();
        DB::table('drawn_numbers')->where('bingo_id', self::BINGO_ID)->delete();
        DB::table('bingo_status_logs')->where('bingo_id', self::BINGO_ID)->delete();
        DB::table('cards')->where('bingo_id', self::BINGO_ID)->delete();
        DB::table('bingo_rounds')->where('bingo_id', self::BINGO_ID)->delete();
        DB::table('bingo_prize_patterns')->where('bingo_id', self::BINGO_ID)->delete();
        DB::table('bingos')->where('id', self::BINGO_ID)->delete();
    }

    private function loadCards(): array
    {
        $path = database_path('seeders/' . self::DATA_FILE);

        if (!is_file($path)) {
            throw new RuntimeException("Arquivo da seed nao encontrado: {$path}");
        }

        $decoded = gzdecode(file_get_contents($path));
        $cards = json_decode($decoded, true);

        if (!is_array($cards) || count($cards) !== 7000) {
            throw new RuntimeException('A seed do Bingo 642 precisa conter exatamente 7000 cartelas.');
        }

        return $cards;
    }
}
