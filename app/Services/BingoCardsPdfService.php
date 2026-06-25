<?php

namespace App\Services;

use App\Models\Bingo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BingoCardsPdfService
{
    private const PROGRESS_TTL_SECONDS = 3600;

    public function __construct(private BingoTicketPdfRenderer $ticketPdfRenderer)
    {
    }

    public function markPending(Bingo $bingo): void
    {
        $bingo->forceFill([
            'cards_pdf_path' => null,
            'cards_pdf_status' => 'pending',
            'cards_pdf_generated_at' => null,
        ])->save();

        $this->setProgress($bingo, 5, 'Geração solicitada. Aguardando o cron processar o PDF.');
    }

    public function generate(Bingo $bingo, ?string $memoryLimit = null, ?int $timeout = null): string
    {
        @set_time_limit($timeout ?? 300);
        @ini_set('memory_limit', $memoryLimit ?? '512M');

        $bingo->forceFill(['cards_pdf_status' => 'processing'])->save();
        $this->setProgress($bingo, 15, 'Preparando dados do bingo.');

        try {
            $path = 'bingo-card-pdfs/bingo-' . $bingo->id . '-cartelas.pdf';
            $cardsCount = $bingo->cards()->count();
            $this->setProgress($bingo, 35, "Cartelas carregadas ({$cardsCount}). Montando layout do PDF.");

            if ($this->shouldUseTicketRenderer($bingo)) {
                $this->ticketPdfRenderer->render($bingo, $path, function (int $done, int $total) use ($bingo) {
                    $percent = 35 + (int) floor(($done / max(1, $total)) * 55);
                    $this->setProgress($bingo, $percent, "Renderizando PDF: {$done}/{$total} cartelas.");
                });
            } else {
                $bingo->load(['rounds', 'cards' => function ($query) {
                    $query->orderByRaw('CAST(card_number AS UNSIGNED) ASC')->with(['numbers', 'responsible']);
                }]);
                $this->setProgress($bingo, 65, 'Renderizando PDF das cartelas.');

                $pdf = app('dompdf.wrapper');
                $pdf->loadView('pdf.cards', compact('bingo'));
                Storage::disk('local')->put($path, $pdf->output());
            }

            $this->setProgress($bingo, 90, 'Salvando arquivo gerado.');

            $bingo->forceFill([
                'cards_pdf_path' => $path,
                'cards_pdf_status' => 'ready',
                'cards_pdf_generated_at' => now(),
            ])->save();
            $this->setProgress($bingo, 100, 'PDF pronto para baixar.');

            return $path;
        } catch (Throwable $exception) {
            $bingo->forceFill(['cards_pdf_status' => 'failed'])->save();
            $this->setProgress($bingo, 100, 'Falha ao gerar PDF.');

            throw $exception;
        }
    }

    public function progress(Bingo $bingo): array
    {
        if ($bingo->cards_pdf_status === 'ready') {
            return ['percent' => 100, 'message' => 'PDF pronto para baixar.'];
        }

        if ($bingo->cards_pdf_status === 'failed') {
            return ['percent' => 100, 'message' => 'Falha ao gerar PDF.'];
        }

        $progress = Cache::get($this->progressCacheKey($bingo));

        if (is_array($progress)) {
            return $progress;
        }

        return match ($bingo->cards_pdf_status) {
            'processing' => ['percent' => 20, 'message' => 'PDF em processamento.'],
            default => ['percent' => 5, 'message' => 'Geração solicitada. Aguardando o cron processar o PDF.'],
        };
    }

    public function setProgress(Bingo $bingo, int $percent, string $message): void
    {
        Cache::put($this->progressCacheKey($bingo), [
            'percent' => max(0, min(100, $percent)),
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
        ], self::PROGRESS_TTL_SECONDS);
    }

    public function filename(Bingo $bingo): string
    {
        return 'cartelas-' . Str::slug($bingo->name) . '.pdf';
    }

    private function progressCacheKey(Bingo $bingo): string
    {
        return 'bingo-card-pdf-progress-' . $bingo->id;
    }

    private function shouldUseTicketRenderer(Bingo $bingo): bool
    {
        return (bool) $bingo->card_template_path
            || file_exists(public_path('material/img/bingo-ticket-template.jpeg'));
    }
}
