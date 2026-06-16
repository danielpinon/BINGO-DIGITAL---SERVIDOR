<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Services\WinnerDetectionService;

class PublicScreenController extends Controller
{
    public function show(Bingo $bingo)
    {
        $data = $this->screenData($bingo);

        return view('pages.public.screen', $data);
    }

    public function state(Bingo $bingo)
    {
        return response()->json($this->statePayload($bingo))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function stream(Bingo $bingo)
    {
        return response()->stream(function () use ($bingo) {
            @set_time_limit(0);
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');

            while (ob_get_level() > 0) {
                @ob_end_flush();
            }

            $lastSignature = null;
            $lastHeartbeat = time();
            $startedAt = time();

            while (!connection_aborted() && time() - $startedAt < 600) {
                $payload = $this->statePayload(Bingo::findOrFail($bingo->id));
                $signature = md5(json_encode([
                    'round' => $payload['round']['id'] ?? null,
                    'prize' => $payload['round']['prize'] ?? null,
                    'drawn_numbers' => $payload['drawn_numbers'],
                    'last_drawn' => $payload['last_drawn'],
                    'possible_winners' => $payload['possible_winners'],
                ]));

                if ($signature !== $lastSignature) {
                    echo "event: screen-state\n";
                    echo 'data: ' . json_encode($payload) . "\n\n";
                    $lastSignature = $signature;
                    $lastHeartbeat = time();
                    flush();
                } elseif (time() - $lastHeartbeat >= 15) {
                    echo ": keep-alive\n\n";
                    $lastHeartbeat = time();
                    flush();
                }

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function statePayload(Bingo $bingo): array
    {
        $data = $this->screenData($bingo);

        return [
            'bingo' => [
                'id' => $data['bingo']->id,
                'status' => $data['bingo']->status,
                'number_range_start' => $data['bingo']->number_range_start,
                'number_range_end' => $data['bingo']->number_range_end,
                'round_quantity' => $data['bingo']->round_quantity,
            ],
            'round' => $data['round'] ? [
                'id' => $data['round']->id,
                'number' => $data['round']->round_number,
                'prize' => $data['round']->currentPrizePattern?->name,
            ] : null,
            'drawn_numbers' => $data['drawnNumbersList'],
            'last_drawn' => $data['lastDrawn'] ? [
                'number' => $data['lastDrawn']->number,
                'time' => $data['lastDrawn']->drawn_at->format('H:i:s'),
            ] : null,
            'possible_winners' => $data['possibleWinners'],
        ];
    }

    private function screenData(Bingo $bingo): array
    {
        $bingo->load(['rounds.currentPrizePattern']);

        $round = $bingo->currentRound();
        $drawnNumbers = $round ? $round->drawnNumbers()->orderBy('drawn_at')->get(['number', 'drawn_at']) : collect();
        $drawnNumbersList = $drawnNumbers->pluck('number')->toArray();
        $lastDrawn = $drawnNumbers->last();

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

        return compact('bingo', 'round', 'drawnNumbersList', 'lastDrawn', 'possibleWinners');
    }
}
