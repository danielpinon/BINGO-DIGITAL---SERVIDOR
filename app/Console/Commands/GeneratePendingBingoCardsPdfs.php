<?php

namespace App\Console\Commands;

use App\Models\Bingo;
use App\Services\BingoCardsPdfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GeneratePendingBingoCardsPdfs extends Command
{
    protected $signature = 'bingos:generate-card-pdfs
        {--limit=5 : Quantidade maxima de PDFs gerados por execucao}
        {--bingo= : ID de um bingo especifico}
        {--retry-failed : Inclui PDFs com status failed}
        {--force : Regenera mesmo se o PDF ja estiver pronto}';

    protected $description = 'Gera PDFs de cartelas pendentes para execucao via cronjob.';

    public function handle(BingoCardsPdfService $pdfService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $commandLock = Cache::lock('bingos-generate-card-pdfs-command', 300);

        if (!$commandLock->get()) {
            $this->warn('Outra execucao de geracao de PDFs ja esta em andamento.');

            return self::SUCCESS;
        }

        try {
            $bingos = $this->pendingBingosQuery()
                ->withCount('cards')
                ->orderBy('updated_at')
                ->limit($limit)
                ->get();

            if ($bingos->isEmpty()) {
                $this->info('Nenhum PDF pendente para gerar.');

                return self::SUCCESS;
            }

            $generated = 0;
            $failed = 0;

            foreach ($bingos as $bingo) {
                $lock = Cache::lock('generate-cards-pdf-bingo-' . $bingo->id, 300);

                if (!$lock->get()) {
                    $this->warn("Bingo {$bingo->id}: geracao ja em andamento, pulando.");
                    continue;
                }

                try {
                    $this->line("Bingo {$bingo->id}: gerando PDF...");
                    $path = $pdfService->generate($bingo);
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

            $this->info("Concluido. Gerados: {$generated}. Falhas: {$failed}.");

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        } finally {
            $commandLock->release();
        }
    }

    private function pendingBingosQuery()
    {
        $query = Bingo::query()->whereHas('cards');

        if ($this->option('bingo')) {
            $query->whereKey((int) $this->option('bingo'));
        }

        if ($this->option('force')) {
            return $query;
        }

        $statuses = ['pending'];

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
