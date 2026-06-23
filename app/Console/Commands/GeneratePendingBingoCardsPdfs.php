<?php

namespace App\Console\Commands;

use App\Models\Bingo;
use App\Services\BingoCardsPdfService;
use App\Services\CardGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GeneratePendingBingoCardsPdfs extends Command
{
    protected $signature = 'bingos:generate-card-pdfs
        {--limit=5 : Quantidade maxima de PDFs gerados por execucao}
        {--bingo= : ID de um bingo especifico}
        {--retry-failed : Inclui PDFs com status failed}
        {--force : Regenera mesmo se o PDF ja estiver pronto}
        {--memory=1536M : Limite de memoria usado pelo Dompdf no cron}
        {--timeout=900 : Tempo maximo em segundos para cada PDF}';

    protected $description = 'Gera cartelas e PDFs pendentes para execucao via cronjob.';

    public function handle(BingoCardsPdfService $pdfService, CardGeneratorService $cardGenerator): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $memoryLimit = (string) $this->option('memory');
        $timeout = max(300, (int) $this->option('timeout'));
        $lockSeconds = $timeout + 300;
        $commandLock = Cache::lock('bingos-generate-card-pdfs-command', $lockSeconds);

        @set_time_limit($timeout);
        @ini_set('memory_limit', $memoryLimit);

        if (!$commandLock->get()) {
            $this->warn('Outra execucao de geracao de PDFs ja esta em andamento.');

            return self::SUCCESS;
        }

        try {
            $cardGenerationResult = $this->processPendingCardGeneration($cardGenerator, $pdfService, $limit, $lockSeconds);

            if ($cardGenerationResult['processed'] > 0 || $cardGenerationResult['failed'] > 0) {
                $this->info("Concluido. Cartelas: {$cardGenerationResult['processed']}. Falhas: {$cardGenerationResult['failed']}.");

                return $cardGenerationResult['failed'] > 0 ? self::FAILURE : self::SUCCESS;
            }

            $bingos = $this->pendingBingosQuery()
                ->withCount('cards')
                ->orderBy('updated_at')
                ->limit($limit)
                ->get();

            if ($bingos->isEmpty()) {
                if ($cardGenerationResult['processed'] === 0) {
                    $this->info('Nenhuma cartela ou PDF pendente para gerar.');
                }

                return self::SUCCESS;
            }

            $generated = 0;
            $failed = 0;

            foreach ($bingos as $bingo) {
                $lock = Cache::lock('generate-cards-pdf-bingo-' . $bingo->id, $lockSeconds);

                if (!$lock->get()) {
                    $this->warn("Bingo {$bingo->id}: geracao ja em andamento, pulando.");
                    continue;
                }

                try {
                    $this->line("Bingo {$bingo->id}: gerando PDF...");
                    $path = $pdfService->generate($bingo, $memoryLimit, $timeout);
                    $generated++;
                    $this->info("Bingo {$bingo->id}: PDF gerado em {$path}.");
                } catch (Throwable $exception) {
                    $failed++;
                    report($exception);
                    $this->error("Bingo {$bingo->id}: falha ao gerar PDF. " . $exception->getMessage());
                } finally {
                    $lock->release();
                }
            }

            $this->info("Concluido. Cartelas: {$cardGenerationResult['processed']}. PDFs gerados: {$generated}. Falhas: " . ($failed + $cardGenerationResult['failed']) . ".");

            return ($failed + $cardGenerationResult['failed']) > 0 ? self::FAILURE : self::SUCCESS;
        } finally {
            $commandLock->release();
        }
    }

    private function processPendingCardGeneration(
        CardGeneratorService $cardGenerator,
        BingoCardsPdfService $pdfService,
        int $limit,
        int $lockSeconds
    ): array {
        $processed = 0;
        $failed = 0;

        $bingos = Bingo::query()
            ->withCount('cards')
            ->where('status', 'preparation')
            ->where('card_quantity', '>', 0)
            ->whereIn('card_generation_status', ['pending', 'processing', 'failed'])
            ->orderBy('updated_at')
            ->limit($limit)
            ->get()
            ->filter(fn (Bingo $bingo) => (int) $bingo->cards_count < (int) $bingo->card_quantity);

        foreach ($bingos as $bingo) {
            $lock = Cache::lock('generate-cards-bingo-' . $bingo->id, $lockSeconds);

            if (!$lock->get()) {
                $this->warn("Bingo {$bingo->id}: geracao de cartelas ja em andamento, pulando.");
                continue;
            }

            try {
                $currentCount = $bingo->cards()->count();
                $targetCount = (int) $bingo->card_quantity;
                $remaining = max(0, $targetCount - $currentCount);

                if ($remaining === 0) {
                    $bingo->forceFill([
                        'card_generation_status' => 'ready',
                        'card_generation_message' => 'Cartelas geradas.',
                        'card_generation_completed_at' => now(),
                    ])->save();
                    continue;
                }

                $bingo->forceFill([
                    'card_generation_status' => 'processing',
                    'card_generation_message' => "Gerando cartelas {$currentCount}/{$targetCount}.",
                    'card_generation_started_at' => $bingo->card_generation_started_at ?: now(),
                    'card_generation_completed_at' => null,
                ])->save();

                $this->line("Bingo {$bingo->id}: gerando {$remaining} cartelas...");
                $generated = $cardGenerator->generate($bingo, $remaining);
                $newCount = $bingo->cards()->count();
                $processed++;

                if ($newCount >= $targetCount) {
                    $bingo->forceFill([
                        'card_generation_status' => 'ready',
                        'card_generation_message' => 'Cartelas geradas.',
                        'card_generation_completed_at' => now(),
                    ])->save();
                    $pdfService->markPending($bingo->refresh());
                    $this->info("Bingo {$bingo->id}: {$newCount}/{$targetCount} cartelas geradas. PDF pendente.");
                } else {
                    $bingo->forceFill([
                        'card_generation_status' => 'processing',
                        'card_generation_message' => "Geradas {$generated} nesta execucao. Total {$newCount}/{$targetCount}.",
                    ])->save();
                    $this->warn("Bingo {$bingo->id}: geradas {$generated}. Total {$newCount}/{$targetCount}.");
                }
            } catch (Throwable $exception) {
                $failed++;
                report($exception);
                $bingo->forceFill([
                    'card_generation_status' => 'failed',
                    'card_generation_message' => 'Falha ao gerar cartelas: ' . $exception->getMessage(),
                ])->save();
                $this->error("Bingo {$bingo->id}: falha ao gerar cartelas. " . $exception->getMessage());
            } finally {
                $lock->release();
            }
        }

        return compact('processed', 'failed');
    }

    private function pendingBingosQuery()
    {
        $query = Bingo::query()->whereHas('cards')
            ->where(function ($query) {
                $query->whereNull('card_generation_status')
                    ->orWhere('card_generation_status', 'ready');
            });

        if ($this->option('bingo')) {
            $query->whereKey((int) $this->option('bingo'));
        }

        if ($this->option('force')) {
            return $query;
        }

        $statuses = ['pending', 'processing'];

        if ($this->option('retry-failed')) {
            $statuses[] = 'failed';
        }

        return $query->where(function ($query) use ($statuses) {
            $query->whereNull('cards_pdf_status')
                ->orWhereIn('cards_pdf_status', $statuses)
                ->orWhere(function ($query) {
                    $query->where('cards_pdf_status', 'ready')
                        ->where(function ($query) {
                            $query->whereNull('cards_pdf_path')
                                ->orWhere('cards_pdf_path', '');
                        });
                });
        });
    }
}
