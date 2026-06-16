<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\BingoPrizePattern;
use App\Models\BingoRound;
use App\Services\BingoCardsPdfService;
use App\Services\CardGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BingoController extends Controller
{
    public function __construct(
        private CardGeneratorService $cardGenerator,
        private BingoCardsPdfService $pdfService
    )
    {
    }

    public function index()
    {
        $bingos = Bingo::withCount('cards')->withCount('winners')->orderBy('created_at', 'desc')->paginate(10);
        
        $stats = [
            'total' => Bingo::count(),
            'preparation' => Bingo::where('status', 'preparation')->count(),
            'ongoing' => Bingo::where('status', 'ongoing')->count(),
            'finished' => Bingo::where('status', 'finished')->count(),
        ];
        
        return view('pages.bingos.index', compact('bingos', 'stats'));
    }

    public function create()
    {
        $patterns = [
            'line' => 'Linha',
            'quina' => 'Quina',
            'full_card' => 'Cartela Cheia',
            'cross' => 'Cruz',
            'corners' => 'Cantos',
        ];
        return view('pages.bingos.create', compact('patterns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'number_range_start' => 'required|integer|min:1',
            'number_range_end' => 'required|integer|gt:number_range_start',
            'card_quantity' => 'required|integer|min:1',
            'numbers_per_card' => 'required|integer|min:1',
            'round_quantity' => 'required|integer|min:1|max:5',
            'cards_per_page' => 'required|integer|min:1|max:6',
            'card_title' => 'nullable|string|max:30',
            'card_logo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'prize_patterns' => 'required|array|min:1',
            'prize_patterns.*' => 'required|string|in:line,quina,full_card,cross,corners',
        ]);

        $cardTitle = $validated['card_title'] ?? 'BINGO';
        $cardLogoPath = $request->hasFile('card_logo')
            ? $request->file('card_logo')->store('bingo-logos', 'public')
            : null;

        $bingo = Bingo::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'number_range_start' => $validated['number_range_start'],
            'number_range_end' => $validated['number_range_end'],
            'card_quantity' => $validated['card_quantity'],
            'numbers_per_card' => $validated['numbers_per_card'],
            'round_quantity' => $validated['round_quantity'],
            'cards_per_page' => $validated['cards_per_page'],
            'card_title' => $cardTitle ?: 'BINGO',
            'card_logo_path' => $cardLogoPath,
            'status' => 'preparation',
            'created_by' => Auth::id(),
        ]);

        $patternLabels = [
            'line' => 'Linha',
            'quina' => 'Quina',
            'full_card' => 'Cartela Cheia',
            'cross' => 'Cruz',
            'corners' => 'Cantos',
        ];

        foreach ($validated['prize_patterns'] as $index => $patternType) {
            BingoPrizePattern::create([
                'bingo_id' => $bingo->id,
                'name' => $patternLabels[$patternType],
                'pattern_type' => $patternType,
                'pattern_order' => $index + 1,
            ]);
        }

        $this->syncRounds($bingo, (int) $validated['round_quantity']);
        $generatedCards = $this->cardGenerator->generate($bingo, (int) $validated['card_quantity']);
        $bingo->update(['card_quantity' => $bingo->cards()->count()]);
        $this->pdfService->markPending($bingo);

        return redirect()
            ->route('bingos.index')
            ->with('sucesso', 'Bingo criado com sucesso! ' . $generatedCards . ' cartelas geradas. O PDF será preparado em segundo plano.');
    }

    public function show(Bingo $bingo)
    {
        $bingo->load(['prizePatterns', 'rounds.winners', 'cards' => function($q) { $q->limit(8); }, 'winners']);
        return view('pages.bingos.show', compact('bingo'));
    }

    public function edit(Bingo $bingo)
    {
        $patterns = [
            'line' => 'Linha',
            'quina' => 'Quina',
            'full_card' => 'Cartela Cheia',
            'cross' => 'Cruz',
            'corners' => 'Cantos',
        ];
        $bingo->load('prizePatterns');
        return view('pages.bingos.edit', compact('bingo', 'patterns'));
    }

    public function update(Request $request, Bingo $bingo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'number_range_start' => 'required|integer|min:1',
            'number_range_end' => 'required|integer|gt:number_range_start',
            'card_quantity' => 'required|integer|min:1',
            'numbers_per_card' => 'required|integer|min:1',
            'round_quantity' => 'required|integer|min:1|max:5',
            'cards_per_page' => 'required|integer|min:1|max:6',
            'card_title' => 'nullable|string|max:30',
            'card_logo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'remove_card_logo' => 'nullable|boolean',
        ]);

        $cardTitle = $validated['card_title'] ?? 'BINGO';

        $pdfShouldRegenerate = $bingo->name !== $validated['name']
            || (int) $bingo->round_quantity !== (int) $validated['round_quantity']
            || (int) $bingo->cards_per_page !== (int) $validated['cards_per_page']
            || (string) ($bingo->card_title ?? 'BINGO') !== (string) ($cardTitle ?: 'BINGO')
            || $request->hasFile('card_logo')
            || $request->boolean('remove_card_logo');

        $updateData = $validated;
        unset($updateData['card_logo'], $updateData['remove_card_logo']);
        $updateData['card_title'] = $cardTitle ?: 'BINGO';

        if ($request->boolean('remove_card_logo') && $bingo->card_logo_path) {
            Storage::disk('public')->delete($bingo->card_logo_path);
            $updateData['card_logo_path'] = null;
        }

        if ($request->hasFile('card_logo')) {
            if ($bingo->card_logo_path) {
                Storage::disk('public')->delete($bingo->card_logo_path);
            }

            $updateData['card_logo_path'] = $request->file('card_logo')->store('bingo-logos', 'public');
        }

        $bingo->update($updateData);

        if ($bingo->status === 'preparation') {
            $this->syncRounds($bingo, (int) $validated['round_quantity']);
        }

        if ($pdfShouldRegenerate && $bingo->cards()->exists()) {
            $this->pdfService->markPending($bingo);
        }

        return redirect()->route('bingos.index')->with('sucesso', 'Bingo atualizado com sucesso!');
    }

    public function destroy(Bingo $bingo)
    {
        if ($bingo->card_logo_path) {
            Storage::disk('public')->delete($bingo->card_logo_path);
        }

        $bingo->delete();
        return redirect()->route('bingos.index')->with('sucesso', 'Bingo removido com sucesso!');
    }

    public function start(Bingo $bingo)
    {
        if ($bingo->status !== 'preparation') {
            return back()->with('falha', 'O bingo já foi iniciado ou finalizado.');
        }

        $firstPattern = $bingo->prizePatterns()->orderBy('pattern_order')->first();

        if (!$firstPattern) {
            return back()->with('falha', 'Cadastre ao menos um padrão de premiação antes de iniciar.');
        }

        $this->syncRounds($bingo, (int) $bingo->round_quantity);

        $firstRound = $bingo->rounds()->where('round_number', 1)->first();

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $firstPattern?->id,
        ]);

        $firstRound->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $firstPattern->id,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        return redirect()->route('draw.index', $bingo)->with('sucesso', 'Bingo iniciado!');
    }

    public function finish(Bingo $bingo)
    {
        if ($bingo->status !== 'ongoing') {
            return back()->with('falha', 'O bingo não está em andamento.');
        }

        $bingo->activeRound?->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        $bingo->update([
            'status' => 'finished',
            'current_prize_pattern_id' => null,
        ]);

        return redirect()->route('bingos.index')->with('sucesso', 'Bingo finalizado!');
    }

    private function syncRounds(Bingo $bingo, int $quantity): void
    {
        for ($roundNumber = 1; $roundNumber <= $quantity; $roundNumber++) {
            BingoRound::firstOrCreate([
                'bingo_id' => $bingo->id,
                'round_number' => $roundNumber,
            ], [
                'status' => 'pending',
            ]);
        }

        if ($bingo->status === 'preparation') {
            $bingo->rounds()->where('round_number', '>', $quantity)->delete();
        }
    }
}
