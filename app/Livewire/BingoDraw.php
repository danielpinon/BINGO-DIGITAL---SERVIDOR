<?php

namespace App\Livewire;

use App\Models\Bingo;
use App\Models\Card;
use App\Models\DrawnNumber;
use App\Services\WinnerDetectionService;
use Livewire\Component;

class BingoDraw extends Component
{
    public $bingoId;
    public $lastNumber = null;
    public $drawnNumbers = [];
    public $possibleWinners = [];
    public $stats = [];
    public $manualNumber = '';
    public $confirmingWinner = null;

    protected $listeners = ['numberDrawn' => 'refreshDraw'];

    public function mount($bingoId)
    {
        $this->bingoId = $bingoId;
        $this->loadData();
    }

    public function loadData()
    {
        $bingo = Bingo::with(['drawnNumbers', 'cards.numbers', 'prizePatterns'])->find($this->bingoId);
        
        $this->drawnNumbers = $bingo->drawnNumbers->pluck('number')->toArray();
        $this->lastNumber = count($this->drawnNumbers) > 0 ? end($this->drawnNumbers) : null;
        
        $totalNumbers = $bingo->number_range_end - $bingo->number_range_start + 1;
        $remaining = $totalNumbers - count($this->drawnNumbers);
        
        $this->stats = [
            'drawn' => count($this->drawnNumbers),
            'remaining' => $remaining,
            'totalCards' => $bingo->cards()->count(),
        ];

        $detector = new WinnerDetectionService();
        $this->possibleWinners = $detector->getPossibleWinners($bingo, $this->drawnNumbers);
    }

    public function drawNumber()
    {
        $bingo = Bingo::find($this->bingoId);
        
        if ($bingo->status !== 'ongoing') {
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

        $bingo = Bingo::find($this->bingoId);
        $number = (int) $this->manualNumber;

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
            'number' => $number,
            'drawn_at' => now(),
        ]);

        $this->manualNumber = '';
        $this->loadData();
        $this->dispatch('numberDrawn');
    }

    public function undoLast()
    {
        $last = DrawnNumber::where('bingo_id', $this->bingoId)
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
        $bingo = Bingo::find($this->bingoId);
        $detector = new WinnerDetectionService();
        
        $isWinner = $detector->verifyWinner($bingo, $cardId, $this->drawnNumbers);
        
        if ($isWinner) {
            $this->confirmingWinner = $cardId;
        }
    }

    public function render()
    {
        $bingo = Bingo::with(['prizePatterns', 'cards.responsible'])->find($this->bingoId);
        return view('livewire.bingo-draw', compact('bingo'));
    }
}
