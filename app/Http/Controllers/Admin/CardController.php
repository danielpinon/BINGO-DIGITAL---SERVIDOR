<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\Card;
use App\Models\Responsible;
use App\Services\BingoCardsPdfService;
use App\Services\CardGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\PhpExecutableFinder;
use Throwable;

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
        $query = Card::with(['bingo', 'responsible', 'numbers'])
            ->whereHas('bingo');
        
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
        } finally {
            $lock->release();
        }

        return redirect()->route('cards.index', ['bingo_id' => $bingo->id])
            ->with('sucesso', $cards . ' cartelas geradas com sucesso! O PDF será preparado em segundo plano.');
    }

    public function assign(Request $request, Card $card)
    {
        $validated = $request->validate([
            'responsible_id' => 'nullable|exists:responsibles,id',
            'responsible_name' => 'required_without:responsible_id|nullable|string|max:255',
        ]);

        $responsible = null;

        if (!empty($validated['responsible_id'])) {
            $responsible = Responsible::findOrFail($validated['responsible_id']);
        }

        if (!$responsible) {
            $responsibleName = trim($validated['responsible_name'] ?? '');

            if ($responsibleName === '') {
                return back()
                    ->withErrors(['responsible_name' => 'Informe ou selecione um responsável.'])
                    ->withInput();
            }

            $responsible = Responsible::where('name', $responsibleName)->first();

            if (!$responsible) {
                $responsible = Responsible::create([
                    'name' => $responsibleName,
                    'status' => 'active',
                ]);
            } elseif ($responsible->status !== 'active') {
                $responsible->update(['status' => 'active']);
            }
        }

        $card->update([
            'responsible_id' => $responsible->id,
            'status' => 'distributed',
        ]);

        $this->pdfService->markPending($card->bingo);

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

        if (!$bingo->cards()->exists()) {
            return redirect()
                ->route('bingos.index')
                ->with('falha', 'Este bingo ainda não possui cartelas para gerar o PDF.');
        }

        $lock = Cache::lock('generate-cards-pdf-bingo-' . $bingo->id, 300);

        if (!$lock->get()) {
            return redirect()
                ->route('bingos.index')
                ->with('falha', 'O PDF deste bingo já está sendo gerado. Tente novamente em alguns instantes.');
        }

        try {
            $path = Storage::disk('local')->path($this->pdfService->generate($bingo));
            $filename = $this->pdfService->filename($bingo->refresh());

            if ($request->boolean('print')) {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }

            return response()->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('bingos.index')
                ->with('falha', 'Não foi possível gerar o PDF das cartelas. Verifique os dados do bingo e tente novamente.');
        } finally {
            $lock->release();
        }
    }

    public function startPdfGeneration(Request $request, Bingo $bingo)
    {
        if (!$bingo->cards()->exists()) {
            return redirect()
                ->route('bingos.index')
                ->with('falha', 'Este bingo ainda não possui cartelas para gerar o PDF.');
        }

        $this->pdfService->markPending($bingo);
        $this->startPdfGenerationProcess($bingo);

        return redirect()->route('cards.pdf.loading', $bingo);
    }

    public function pdfLoading(Bingo $bingo)
    {
        return view('pages.cards.pdf-loading', compact('bingo'));
    }

    public function pdfProgress(Bingo $bingo)
    {
        $bingo->refresh();
        $progress = $this->pdfService->progress($bingo);
        $isReady = $bingo->cards_pdf_status === 'ready'
            && $bingo->cards_pdf_path
            && Storage::disk('local')->exists($bingo->cards_pdf_path);

        return response()->json([
            'status' => $bingo->cards_pdf_status ?? 'pending',
            'status_label' => match ($bingo->cards_pdf_status) {
                'ready' => 'Pronto',
                'processing' => 'Processando',
                'failed' => 'Falhou',
                default => 'Aguardando',
            },
            'percent' => $progress['percent'],
            'message' => $progress['message'],
            'updated_at' => $progress['updated_at'] ?? null,
            'ready' => $isReady,
            'failed' => $bingo->cards_pdf_status === 'failed',
            'download_url' => $isReady ? route('cards.export', ['bingo_id' => $bingo->id]) : null,
        ]);
    }

    private function startPdfGenerationProcess(Bingo $bingo): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $php = (new PhpExecutableFinder())->find(false) ?: PHP_BINARY;
        $command = implode(' ', [
            escapeshellarg($php),
            escapeshellarg(base_path('artisan')),
            'bingos:generate-card-pdfs',
            '--limit=1',
            '--force',
            '--bingo=' . escapeshellarg((string) $bingo->id),
            '--memory=1536M',
            '--timeout=900',
            '> /dev/null 2>&1 &',
        ]);

        exec($command);
    }
}
