<?php

namespace App\Livewire;

use App\Models\Bingo;
use App\Models\DrawnNumber;
use App\Models\Winner;
use App\Services\WinnerDetectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BingoDraw extends Component
{
    public $bingoId;
    public $roundId = null;
    public $roundNumber = 1;
    public $roundQuantity = 1;
    public $currentPrizeName = null;
    public $lastNumber = null;
    public $drawnNumbers = [];
    public $possibleWinners = [];
    public $stats = [];
    public $manualNumber = '';

    protected $listeners = ['numberDrawn' => 'refreshDraw'];

    public function mount($bingoId)
    {
        $this->bingoId = $bingoId;
        $this->loadData();
    }

    public function loadData()
    {
        $bingo = Bingo::with(['rounds.currentPrizePattern', 'cards.numbers', 'prizePatterns'])->find($this->bingoId);
        $round = $bingo->currentRound();

        if (!$round) {
            $this->drawnNumbers = [];
            $this->lastNumber = null;
            $this->possibleWinners = [];
            $this->stats = [
                'drawn' => 0,
                'remaining' => $bingo->number_range_end - $bingo->number_range_start + 1,
                'totalCards' => $bingo->cards()->count(),
            ];
            return;
        }
        
        $this->roundId = $round->id;
        $this->roundNumber = $round->round_number;
        $this->roundQuantity = $bingo->round_quantity;
        $this->currentPrizeName = $round->currentPrizePattern?->name;
        $this->drawnNumbers = $round->drawnNumbers()->pluck('number')->toArray();
        $this->lastNumber = count($this->drawnNumbers) > 0 ? end($this->drawnNumbers) : null;
        
        $totalNumbers = $bingo->number_range_end - $bingo->number_range_start + 1;
        $remaining = $totalNumbers - count($this->drawnNumbers);
        
        $this->stats = [
            'drawn' => count($this->drawnNumbers),
            'remaining' => $remaining,
            'totalCards' => $bingo->cards()->count(),
        ];

        $detector = new WinnerDetectionService();
        $this->possibleWinners = $detector->getPossibleWinners($bingo, $this->drawnNumbers, $round);
    }

    public function drawNumber()
    {
        $bingo = Bingo::with('rounds')->find($this->bingoId);
        $round = $bingo->activeRound;
        
        if ($bingo->status !== 'ongoing' || !$round) {
            $this->dispatch('notify', ['message' => 'O bingo não está em andamento.', 'type' => 'error']);
            return;
        }

        $available = range($bingo->number_range_start, $bingo->number_range_end);
        $available = array_diff($available, $this->drawnNumbers);
        
        if (empty($available)) {
            $this->dispatch('notify', ['message' => 'Todos os números já foram sorteados!', 'type' => 'warning']);
            return;
        }

        $number = $available[array_rand($available)];
        
        DrawnNumber::create([
            'bingo_id' => $this->bingoId,
            'bingo_round_id' => $round->id,
            'number' => $number,
            'drawn_at' => now(),
        ]);

        $this->loadData();
        $this->dispatch('numberDrawn');
    }

    public function addManualNumber()
    {
        $this->validate([
            'manualNumber' => 'required|integer|min:1|max:99',
        ]);

        $bingo = Bingo::with('rounds')->find($this->bingoId);
        $round = $bingo->activeRound;
        $number = (int) $this->manualNumber;

        if (!$round) {
            $this->dispatch('notify', ['message' => 'O bingo não está em andamento.', 'type' => 'error']);
            return;
        }

        if ($number < $bingo->number_range_start || $number > $bingo->number_range_end) {
            $this->dispatch('notify', ['message' => 'Número fora do intervalo permitido.', 'type' => 'error']);
            return;
        }

        if (in_array($number, $this->drawnNumbers)) {
            $this->dispatch('notify', ['message' => 'Este número já foi sorteado.', 'type' => 'warning']);
            return;
        }

        DrawnNumber::create([
            'bingo_id' => $this->bingoId,
            'bingo_round_id' => $round->id,
            'number' => $number,
            'drawn_at' => now(),
        ]);

        $this->manualNumber = '';
        $this->loadData();
        $this->dispatch('numberDrawn');
    }

    public function undoLast()
    {
        $bingo = Bingo::find($this->bingoId);
        $round = $bingo?->activeRound;

        if (!$round) {
            return;
        }

        $last = DrawnNumber::where('bingo_round_id', $round->id)
            ->orderBy('drawn_at', 'desc')
            ->first();

        if ($last) {
            $last->delete();
            $this->loadData();
            $this->dispatch('numberDrawn');
        }
    }

    public function confirmWinner($cardId)
    {
        $bingo = Bingo::with(['prizePatterns', 'rounds'])->find($this->bingoId);
        $round = $bingo->activeRound;

        if (!$round || !$round->currentPrizePattern) {
            $this->dispatch('notify', ['message' => 'Nenhuma rodada ou prêmio ativo encontrado.', 'type' => 'error']);
            return;
        }

        $detector = new WinnerDetectionService();
        
        $isWinner = $detector->verifyWinner($bingo, $cardId, $this->drawnNumbers, $round);
        
        if (!$isWinner) {
            $this->dispatch('notify', ['message' => 'A cartela ainda não bateu para o prêmio atual.', 'type' => 'warning']);
            return;
        }

        DB::transaction(function () use ($bingo, $round, $cardId) {
            $card = $bingo->cards()->findOrFail($cardId);
            $currentPattern = $round->currentPrizePattern;

            Winner::firstOrCreate([
                'bingo_round_id' => $round->id,
                'card_id' => $card->id,
                'prize_pattern_id' => $currentPattern->id,
            ], [
                'bingo_id' => $bingo->id,
                'responsible_id' => $card->responsible_id,
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
            ]);

            $configuredNextRound = $bingo->rounds()
                ->where('round_number', '>', $round->round_number)
                ->where('round_number', '<=', $bingo->round_quantity)
                ->whereNotNull('current_prize_pattern_id')
                ->orderBy('round_number')
                ->first();

            if ($configuredNextRound) {
                $round->update([
                    'status' => 'finished',
                    'current_prize_pattern_id' => null,
                    'finished_at' => now(),
                ]);

                $configuredNextRound->update([
                    'status' => 'ongoing',
                    'started_at' => now(),
                    'finished_at' => null,
                ]);

                $bingo->update([
                    'current_prize_pattern_id' => $configuredNextRound->current_prize_pattern_id,
                ]);

                return;
            }

            $nextPattern = $bingo->prizePatterns()
                ->where('pattern_order', '>', $currentPattern->pattern_order)
                ->orderBy('pattern_order')
                ->first();

            if ($nextPattern) {
                $round->drawnNumbers()->delete();
                $round->update(['current_prize_pattern_id' => $nextPattern->id]);
                $bingo->update(['current_prize_pattern_id' => $nextPattern->id]);
                return;
            }

            $round->update([
                'status' => 'finished',
                'current_prize_pattern_id' => null,
                'finished_at' => now(),
            ]);

            $nextRound = $bingo->rounds()
                ->where('round_number', '>', $round->round_number)
                ->where('round_number', '<=', $bingo->round_quantity)
                ->orderBy('round_number')
                ->first();

            $firstPattern = $bingo->prizePatterns()->orderBy('pattern_order')->first();

            if ($nextRound && $firstPattern) {
                $nextRound->update([
                    'status' => 'ongoing',
                    'current_prize_pattern_id' => $firstPattern->id,
                    'started_at' => now(),
                    'finished_at' => null,
                ]);

                $bingo->update([
                    'current_prize_pattern_id' => $firstPattern->id,
                ]);

                return;
            }

            $bingo->update([
                'status' => 'finished',
                'current_prize_pattern_id' => null,
            ]);
        });

        $this->loadData();
        $this->dispatch('numberDrawn');
        $this->dispatch('notify', ['message' => 'Ganhador validado com sucesso!', 'type' => 'success']);
    }

    public function render()
    {
        $bingo = Bingo::with(['prizePatterns', 'rounds', 'cards.responsible'])->find($this->bingoId);
        return view('livewire.bingo-draw', compact('bingo'));
    }
}
