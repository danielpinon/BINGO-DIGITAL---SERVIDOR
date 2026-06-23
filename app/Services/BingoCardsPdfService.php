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
            $bingo->load(['rounds', 'cards' => function ($query) {
                $query->orderBy('card_number')->with(['numbers', 'responsible']);
            }]);
            $this->setProgress($bingo, 35, 'Cartelas carregadas. Montando layout do PDF.');

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.cards', compact('bingo'));
            $this->setProgress($bingo, 65, 'Renderizando PDF das cartelas.');

            $path = 'bingo-card-pdfs/bingo-' . $bingo->id . '-cartelas.pdf';
            Storage::disk('local')->put($path, $pdf->output());
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
}
