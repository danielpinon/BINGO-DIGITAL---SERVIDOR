<?php

namespace App\Services;

use App\Models\Bingo;
use App\Models\BingoRound;
use App\Models\Card;

class WinnerDetectionService
{
    public function getPossibleWinners(Bingo $bingo, array $drawnNumbers, ?BingoRound $round = null): array
    {
        $round = $round ?: $bingo->currentRound();
        $currentPattern = $round?->currentPrizePattern ?: $bingo->currentPrizePattern;
        if (!$currentPattern) {
            return [];
        }

        $cards = Card::with('numbers')
            ->where('bingo_id', $bingo->id)
            ->when($bingo->only_linked_cards, fn ($query) => $query->whereNotNull('responsible_id'))
            ->get();
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

            $missingForPattern = $this->getMissingForPattern($currentPattern->pattern_type, $marked, $grid);
            $isWinner = count($missingForPattern) === 0;

            if ($isWinner || count($missingForPattern) <= 2) {
                $possibleWinners[] = [
                    'card' => $card,
                    'missing' => $isWinner ? [] : $missingForPattern,
                    'is_winner' => $isWinner,
                    'is_close' => !$isWinner,
                    'pattern_type' => $currentPattern->pattern_type,
                ];
            }
        }

        usort($possibleWinners, function($a, $b) {
            return count($a['missing']) <=> count($b['missing']);
        });

        return array_slice($possibleWinners, 0, 10);
    }

    public function verifyWinner(Bingo $bingo, int $cardId, array $drawnNumbers, ?BingoRound $round = null): bool
    {
        $round = $round ?: $bingo->currentRound();
        $currentPattern = $round?->currentPrizePattern ?: $bingo->currentPrizePattern;
        if (!$currentPattern) {
            return false;
        }

        $card = Card::with('numbers')
            ->where('id', $cardId)
            ->where('bingo_id', $bingo->id)
            ->when($bingo->only_linked_cards, fn ($query) => $query->whereNotNull('responsible_id'))
            ->first();
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

    private function getMissingForPattern(string $patternType, array $marked, array $grid): array
    {
        return match($patternType) {
            'line' => $this->missingForLine($marked, $grid),
            'quina' => $this->missingForQuina($marked, $grid),
            'full_card' => $this->missingForFullCard($marked, $grid),
            'cross' => $this->missingForCross($marked, $grid),
            'corners' => $this->missingForCorners($marked, $grid),
            default => [],
        };
    }

    private function missingForLine(array $marked, array $grid): array
    {
        $bestMissing = null;

        for ($row = 0; $row < 5; $row++) {
            $missing = [];
            for ($col = 0; $col < 5; $col++) {
                if (!$marked[$row][$col]) {
                    $missing[] = $grid[$row][$col];
                }
            }

            if ($bestMissing === null || count($missing) < count($bestMissing)) {
                $bestMissing = $missing;
            }
        }

        return $bestMissing ?? [];
    }

    private function missingForQuina(array $marked, array $grid): array
    {
        $missing = [];
        $markedCount = 0;

        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                if ($marked[$row][$col]) {
                    $markedCount++;
                } else {
                    $missing[] = $grid[$row][$col];
                }
            }
        }

        if ($markedCount >= 5) {
            return [];
        }

        return array_slice($missing, 0, 5 - $markedCount);
    }

    private function missingForFullCard(array $marked, array $grid): array
    {
        $missing = [];
        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                if (!$marked[$row][$col]) {
                    $missing[] = $grid[$row][$col];
                }
            }
        }
        return $missing;
    }

    private function missingForCross(array $marked, array $grid): array
    {
        $required = [];

        for ($col = 0; $col < 5; $col++) {
            $required[] = [2, $col];
        }

        for ($row = 0; $row < 5; $row++) {
            $required[] = [$row, 2];
        }

        $unique = [];
        foreach ($required as [$row, $col]) {
            $unique[$row . '-' . $col] = [$row, $col];
        }

        $missing = [];
        foreach ($unique as [$row, $col]) {
            if (!$marked[$row][$col]) {
                $missing[] = $grid[$row][$col];
            }
        }

        return $missing;
    }

    private function missingForCorners(array $marked, array $grid): array
    {
        $corners = [[0, 0], [0, 4], [4, 0], [4, 4]];
        $missing = [];

        foreach ($corners as [$row, $col]) {
            if (!$marked[$row][$col]) {
                $missing[] = $grid[$row][$col];
            }
        }

        return $missing;
    }
}
