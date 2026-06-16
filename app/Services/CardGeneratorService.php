<?php

namespace App\Services;

use App\Models\Bingo;
use App\Models\Card;
use App\Models\CardNumber;
use Illuminate\Support\Facades\DB;

class CardGeneratorService
{
    public function generate(Bingo $bingo, int $quantity): int
    {
        return DB::transaction(function () use ($bingo, $quantity) {
            $generated = 0;
            $attempts = 0;
            $maxAttempts = $quantity * 10;

            $rangeStart = $bingo->number_range_start;
            $rangeEnd = $bingo->number_range_end;
            $numbersPerCard = $bingo->numbers_per_card;

            $existingCards = Card::where('bingo_id', $bingo->id)
                ->lockForUpdate()
                ->with('numbers')
                ->get();

            $nextCardNumber = $existingCards
                ->pluck('card_number')
                ->map(fn ($cardNumber) => (int) $cardNumber)
                ->max() + 1;

            $existingHashes = $existingCards
                ->map(function ($card) {
                    $nums = $card->numbers->pluck('number')->sort()->values()->toArray();
                    return implode(',', $nums);
                })
                ->flip()
                ->toArray();

            while ($generated < $quantity && $attempts < $maxAttempts) {
                $attempts++;

                $numbers = $this->generateUniqueNumbers($rangeStart, $rangeEnd, $numbersPerCard);
                sort($numbers);
                $hash = implode(',', $numbers);

                if (isset($existingHashes[$hash])) {
                    continue;
                }

                $existingHashes[$hash] = true;

                $card = Card::create([
                    'bingo_id' => $bingo->id,
                    'responsible_id' => null,
                    'card_number' => str_pad($nextCardNumber, 3, '0', STR_PAD_LEFT),
                    'status' => 'available',
                ]);

                $nextCardNumber++;

                $grid = $this->arrangeInGrid($numbers);
                foreach ($grid as $row => $cols) {
                    foreach ($cols as $col => $number) {
                        CardNumber::create([
                            'card_id' => $card->id,
                            'row' => $row,
                            'col' => $col,
                            'number' => $number,
                        ]);
                    }
                }

                $generated++;
            }

            return $generated;
        });
    }
    
    private function generateUniqueNumbers(int $min, int $max, int $count): array
    {
        $range = range($min, $max);
        shuffle($range);
        return array_slice($range, 0, $count);
    }
    
    private function arrangeInGrid(array $numbers): array
    {
        $grid = [];
        $index = 0;
        
        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                if (isset($numbers[$index])) {
                    $grid[$row][$col] = $numbers[$index];
                    $index++;
                }
            }
        }
        
        return $grid;
    }
}
