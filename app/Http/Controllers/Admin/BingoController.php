<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\BingoPrizePattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BingoController extends Controller
{
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
            'prize_patterns' => 'required|array|min:1',
            'prize_patterns.*' => 'required|string|in:line,quina,full_card,cross,corners',
        ]);

        $bingo = Bingo::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'number_range_start' => $validated['number_range_start'],
            'number_range_end' => $validated['number_range_end'],
            'card_quantity' => $validated['card_quantity'],
            'numbers_per_card' => $validated['numbers_per_card'],
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

        return redirect()->route('bingos.index')->with('sucesso', 'Bingo criado com sucesso!');
    }

    public function show(Bingo $bingo)
    {
        $bingo->load(['prizePatterns', 'cards' => function($q) { $q->limit(8); }, 'winners']);
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
        ]);

        $bingo->update($validated);

        return redirect()->route('bingos.index')->with('sucesso', 'Bingo atualizado com sucesso!');
    }

    public function destroy(Bingo $bingo)
    {
        $bingo->delete();
        return redirect()->route('bingos.index')->with('sucesso', 'Bingo removido com sucesso!');
    }

    public function start(Bingo $bingo)
    {
        if ($bingo->status !== 'preparation') {
            return back()->with('falha', 'O bingo já foi iniciado ou finalizado.');
        }

        $firstPattern = $bingo->prizePatterns()->where('is_completed', false)->orderBy('pattern_order')->first();

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $firstPattern?->id,
        ]);

        return redirect()->route('draw.index', $bingo)->with('sucesso', 'Bingo iniciado!');
    }

    public function finish(Bingo $bingo)
    {
        if ($bingo->status !== 'ongoing') {
            return back()->with('falha', 'O bingo não está em andamento.');
        }

        $bingo->update(['status' => 'finished']);

        return redirect()->route('bingos.index')->with('sucesso', 'Bingo finalizado!');
    }
}
