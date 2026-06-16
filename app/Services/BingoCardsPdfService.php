<?php

namespace App\Services;

use App\Models\Bingo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BingoCardsPdfService
{
    public function markPending(Bingo $bingo): void
    {
        $bingo->forceFill([
            'cards_pdf_path' => null,
            'cards_pdf_status' => 'pending',
            'cards_pdf_generated_at' => null,
        ])->save();
    }

    public function generate(Bingo $bingo): string
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $bingo->forceFill(['cards_pdf_status' => 'processing'])->save();

        try {
            $bingo->load(['rounds', 'cards' => function ($query) {
                $query->orderBy('card_number')->with(['numbers', 'responsible']);
            }]);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.cards', compact('bingo'));

            $path = 'bingo-card-pdfs/bingo-' . $bingo->id . '-cartelas.pdf';
            Storage::disk('local')->put($path, $pdf->output());

            $bingo->forceFill([
                'cards_pdf_path' => $path,
                'cards_pdf_status' => 'ready',
                'cards_pdf_generated_at' => now(),
            ])->save();

            return $path;
        } catch (Throwable $exception) {
            $bingo->forceFill(['cards_pdf_status' => 'failed'])->save();

            throw $exception;
        }
    }

    public function filename(Bingo $bingo): string
    {
        return 'cartelas-' . Str::slug($bingo->name) . '.pdf';
    }
}
