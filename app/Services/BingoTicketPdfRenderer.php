<?php

namespace App\Services;

use App\Models\Bingo;
use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;

class BingoTicketPdfRenderer
{
    private const POINTS_PER_MM = 72 / 25.4;
    private const PAGE_WIDTH = 595.28;
    private const PAGE_HEIGHT = 841.89;

    private const MAIN_X = [37.7, 51.8, 66.0, 80.2, 94.4];
    private const MAIN_Y = [94.2, 107.2, 120.2, 133.2, 146.2];
    private const LEFT_X = [22.7, 33.0, 43.4, 53.7, 64.1];
    private const LEFT_Y = [193.2, 202.4, 211.4, 220.5, 229.6];
    private const RIGHT_X = [110.5, 120.9, 131.3, 141.7, 152.1];
    private const RIGHT_Y = [168.8, 177.9, 187.0, 196.1, 205.2];

    public function render(Bingo $bingo, string $path, ?callable $progress = null): void
    {
        $dompdf = new Dompdf();
        $canvas = new CPDF([0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT], 'portrait', $dompdf);
        $font = $dompdf->getFontMetrics()->getFont('times', 'bold');
        $templatePath = $this->templatePath($bingo);
        $total = max(1, (int) $bingo->cards()->count());
        $drawn = 0;
        $firstPage = true;

        $bingo->cards()
            ->with('numbers')
            ->orderByRaw('CAST(card_number AS UNSIGNED) ASC')
            ->chunk(100, function ($cards) use ($canvas, $font, $templatePath, $total, &$drawn, &$firstPage, $progress) {
                foreach ($cards as $card) {
                    if (!$firstPage) {
                        $canvas->new_page();
                    }

                    $firstPage = false;
                    $grid = $card->grid;

                    $canvas->image($templatePath, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT);

                    $this->drawCardNumber($canvas, $font, (string) $card->card_number, false);
                    $this->drawCardNumber($canvas, $font, (string) $card->card_number, true);

                    foreach ($grid as $row => $cols) {
                        foreach ($cols as $col => $number) {
                            $this->drawNumber($canvas, $font, $number, self::MAIN_X[(int) $col], self::MAIN_Y[(int) $row], 18);
                            $this->drawNumber($canvas, $font, $number, self::LEFT_X[(int) $col], self::LEFT_Y[(int) $row], 12);
                            $this->drawNumber($canvas, $font, $number, self::RIGHT_X[(int) $col], self::RIGHT_Y[(int) $row], 12);
                        }
                    }

                    $drawn++;

                    if ($progress && ($drawn % 250 === 0 || $drawn === $total)) {
                        $progress($drawn, $total);
                    }
                }
            });

        Storage::disk('local')->put($path, $canvas->output());
    }

    private function templatePath(Bingo $bingo): string
    {
        if ($bingo->card_template_path && Storage::disk('public')->exists($bingo->card_template_path)) {
            return Storage::disk('public')->path($bingo->card_template_path);
        }

        return public_path('material/img/bingo-ticket-template.jpeg');
    }

    private function drawCardNumber(CPDF $canvas, string $font, string $text, bool $bottom): void
    {
        if ($bottom) {
            $this->drawCentered($canvas, $font, $text, 168.5, 252.0, strlen($text) >= 4 ? 15 : 16);

            return;
        }

        $this->drawCentered($canvas, $font, $text, 169.5, 23.0, strlen($text) >= 4 ? 17 : 18);
    }

    private function drawNumber(CPDF $canvas, string $font, int $number, float $xMm, float $yMm, int $size): void
    {
        $this->drawCentered($canvas, $font, str_pad((string) $number, 2, '0', STR_PAD_LEFT), $xMm, $yMm, $size);
    }

    private function drawCentered(CPDF $canvas, string $font, string $text, float $xMm, float $yMm, int $size): void
    {
        $x = $this->mm($xMm) - ($canvas->get_text_width($text, $font, $size) / 2);
        $y = $this->mm($yMm) - ($size * 0.35);

        $canvas->text($x, $y, $text, $font, $size, [0.05, 0.05, 0.05]);
    }

    private function mm(float $value): float
    {
        return $value * self::POINTS_PER_MM;
    }
}
