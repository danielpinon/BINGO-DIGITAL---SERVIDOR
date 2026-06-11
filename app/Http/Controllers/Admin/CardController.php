<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\Card;
use App\Models\Responsible;
use App\Services\CardGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CardController extends Controller
{
    protected $cardGenerator;

    public function __construct(CardGeneratorService $cardGenerator)
    {
        $this->cardGenerator = $cardGenerator;
    }

    public function index(Request $request)
    {
        $query = Card::with(['bingo', 'responsible', 'numbers']);
        
        if ($request->filled('bingo_id')) {
            $query->where('bingo_id', $request->bingo_id);
        }
        
        if ($request->filled('responsible_id')) {
            $query->where('responsible_id', $request->responsible_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $cards = $query->orderBy('bingo_id')->orderBy('card_number')->paginate(20);
        $bingos = Bingo::orderBy('name')->get();
        $responsibles = Responsible::where('status', 'active')->orderBy('name')->get();
        
        return view('pages.cards.index', compact('cards', 'bingos', 'responsibles'));
    }

    public function generateForm()
    {
        $bingos = Bingo::where('status', 'preparation')->orderBy('name')->get();
        return view('pages.cards.generate', compact('bingos'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'bingo_id' => 'required|exists:bingos,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        $bingo = Bingo::findOrFail($validated['bingo_id']);
        
        if ($bingo->status !== 'preparation') {
            return back()->with('falha', 'Só é possível gerar cartelas para bingos em preparação.');
        }

        $lock = Cache::lock('generate-cards-bingo-' . $bingo->id, 30);

        if (!$lock->get()) {
            return back()->with('falha', 'As cartelas deste bingo já estão sendo geradas. Aguarde alguns segundos.');
        }

        try {
            $cards = $this->cardGenerator->generate($bingo, $validated['quantity']);
            $bingo->update(['card_quantity' => $bingo->cards()->count()]);
        } finally {
            $lock->release();
        }

        return redirect()->route('cards.export', ['bingo_id' => $bingo->id, 'print' => 1])
            ->with('sucesso', $cards . ' cartelas geradas com sucesso!');
    }

    public function assign(Request $request, Card $card)
    {
        $validated = $request->validate([
            'responsible_id' => 'required|exists:responsibles,id',
        ]);

        $card->update([
            'responsible_id' => $validated['responsible_id'],
            'status' => 'distributed',
        ]);

        return back()->with('sucesso', 'Cartela atribuída com sucesso!');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'bingo_id' => 'required|exists:bingos,id',
        ]);

        $bingo = Bingo::with(['rounds', 'cards' => function ($query) {
            $query->orderBy('card_number')->with(['numbers', 'responsible']);
        }])->findOrFail($validated['bingo_id']);
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.cards', compact('bingo'));

        if ($request->boolean('print')) {
            return $pdf->stream('cartelas-' . $bingo->name . '.pdf');
        }
        
        return $pdf->download('cartelas-' . $bingo->name . '.pdf');
    }
}
