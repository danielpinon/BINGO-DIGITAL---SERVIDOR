<?php

namespace App\Services;

use App\Models\Bingo;
use App\Models\Card;

class WinnerDetectionService
{
    public function getPossibleWinners(Bingo $bingo, array $drawnNumbers): array
    {
        $currentPattern = $bingo->currentPrizePattern;
        if (!$currentPattern) {
            return [];
        }

        $cards = Card::with('numbers')->where('bingo_id', $bingo->id)->get();
        $possibleWinners = [];
        $drawnSet = array_flip($drawnNumbers);

        foreach ($cards as $card) {
            $grid = $card->grid;
            $marked = [];
            $missing = [];

            foreach ($grid as $row => $cols) {
                foreach ($cols as $col => $number) {
                    $isMarked = isset($drawnSet[$number]);
                    $marked[$row][$col] = $isMarked;
                    if (!$isMarked) {
                        $missing[] = $number;
                    }
                }
            }

            $isWinner = $this->checkPattern($currentPattern->pattern_type, $marked);

            if ($isWinner) {
                $possibleWinners[] = [
                    'card' => $card,
                    'missing' => $missing,
                    'is_winner' => true,
                    'is_close' => false,
                    'pattern_type' => $currentPattern->pattern_type,
                ];
            }
        }

        usort($possibleWinners, function($a, $b) {
            return count($a['missing']) <=> count($b['missing']);
        });

        return array_slice($possibleWinners, 0, 10);
    }

    public function verifyWinner(Bingo $bingo, int $cardId, array $drawnNumbers): bool
    {
        $currentPattern = $bingo->currentPrizePattern;
        if (!$currentPattern) {
            return false;
        }

        $card = Card::with('numbers')->find($cardId);
        if (!$card) {
            return false;
        }

        $grid = $card->grid;
        $drawnSet = array_flip($drawnNumbers);
        $marked = [];

        foreach ($grid as $row => $cols) {
            foreach ($cols as $col => $number) {
                $marked[$row][$col] = isset($drawnSet[$number]);
            }
        }

        return $this->checkPattern($currentPattern->pattern_type, $marked);
    }

    private function checkPattern(string $patternType, array $marked): bool
    {
        return match($patternType) {
            'line' => $this->checkLine($marked),
            'quina' => $this->checkQuina($marked),
            'full_card' => $this->checkFullCard($marked),
            'cross' => $this->checkCross($marked),
            'corners' => $this->checkCorners($marked),
            default => false,
        };
    }

    private function checkLine(array $marked): bool
    {
        for ($row = 0; $row < 5; $row++) {
            $allMarked = true;
            for ($col = 0; $col < 5; $col++) {
                if (!$marked[$row][$col]) {
                    $allMarked = false;
                    break;
                }
            }
            if ($allMarked) return true;
        }
        return false;
    }

    private function checkQuina(array $marked): bool
    {
        $count = 0;
        foreach ($marked as $row => $cols) {
            foreach ($cols as $col => $isMarked) {
                if ($isMarked) $count++;
            }
        }
        return $count >= 5;
    }

    private function checkFullCard(array $marked): bool
    {
        foreach ($marked as $row => $cols) {
            foreach ($cols as $col => $isMarked) {
                if (!$isMarked) return false;
            }
        }
        return true;
    }

    private function checkCross(array $marked): bool
    {
        $middleRow = true;
        for ($col = 0; $col < 5; $col++) {
            if (!$marked[2][$col]) {
                $middleRow = false;
                break;
            }
        }

        $middleCol = true;
        for ($row = 0; $row < 5; $row++) {
            if (!$marked[$row][2]) {
                $middleCol = false;
                break;
            }
        }

        return $middleRow && $middleCol;
    }

    private function checkCorners(array $marked): bool
    {
        return $marked[0][0] && $marked[0][4] && $marked[4][0] && $marked[4][4];
    }
}
