<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Services\WinnerDetectionService;

class PublicScreenController extends Controller
{
    public function show(Bingo $bingo)
    {
        $bingo->load(['rounds.currentPrizePattern', 'rounds.drawnNumbers']);

        $round = $bingo->currentRound();
        $drawnNumbersList = $round ? $round->drawnNumbers()->pluck('number')->toArray() : [];
        $lastDrawn = $round ? $round->drawnNumbers()->orderByDesc('drawn_at')->first() : null;

        $possibleWinners = [];
        if ($round) {
            $detector = new WinnerDetectionService();
            $possibleWinners = collect($detector->getPossibleWinners($bingo, $drawnNumbersList, $round))
                ->where('is_close', true)
                ->pluck('missing')
                ->map(fn ($missing) => array_values($missing))
                ->values()
                ->toArray();
        }

        return view('pages.public.screen', compact('bingo', 'round', 'drawnNumbersList', 'lastDrawn', 'possibleWinners'));
    }
}
