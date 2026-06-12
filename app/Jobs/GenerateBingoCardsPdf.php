<?php

namespace App\Jobs;

use App\Models\Bingo;
use App\Services\BingoCardsPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateBingoCardsPdf implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function __construct(public int $bingoId)
    {
    }

    public function handle(BingoCardsPdfService $pdfService): void
    {
        $bingo = Bingo::findOrFail($this->bingoId);

        $pdfService->generate($bingo);
    }

    public function failed(Throwable $exception): void
    {
        Bingo::whereKey($this->bingoId)->update([
            'cards_pdf_status' => 'failed',
        ]);
    }
}
