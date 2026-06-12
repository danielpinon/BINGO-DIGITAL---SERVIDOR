<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateBingoCardsPdf;
use App\Models\Bingo;
use App\Models\Card;
use App\Models\Responsible;
use App\Services\BingoCardsPdfService;
use App\Services\CardGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CardController extends Controller
{
    protected $cardGenerator;
    protected $pdfService;

    public function __construct(CardGeneratorService $cardGenerator, BingoCardsPdfService $pdfService)
    {
        $this->cardGenerator = $cardGenerator;
        $this->pdfService = $pdfService;
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
            $this->pdfService->markPending($bingo);
            GenerateBingoCardsPdf::dispatch($bingo->id)->afterResponse();
        } finally {
            $lock->release();
        }

        return redirect()->route('cards.index', ['bingo_id' => $bingo->id])
            ->with('sucesso', $cards . ' cartelas geradas com sucesso! O PDF será preparado em segundo plano.');
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

        $this->pdfService->markPending($card->bingo);
        GenerateBingoCardsPdf::dispatch($card->bingo_id)->afterResponse();

        return back()->with('sucesso', 'Cartela atribuída com sucesso! O PDF será atualizado em segundo plano.');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'bingo_id' => 'required|exists:bingos,id',
        ]);

        $bingo = Bingo::findOrFail($validated['bingo_id']);

        if ($bingo->cards_pdf_path && Storage::disk('local')->exists($bingo->cards_pdf_path)) {
            $path = Storage::disk('local')->path($bingo->cards_pdf_path);
            $filename = $this->pdfService->filename($bingo);

            if ($request->boolean('print')) {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }

            return response()->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        if ($bingo->cards_pdf_status !== 'processing') {
            $this->pdfService->markPending($bingo);
            GenerateBingoCardsPdf::dispatch($bingo->id)->afterResponse();
        }

        return redirect()
            ->route('bingos.index')
            ->with('sucesso', 'O PDF das cartelas está sendo gerado em segundo plano. Tente baixar novamente em alguns instantes.');
    }
}
