<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\Card;
use App\Models\DrawnNumber;
use App\Models\Responsible;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $bingoId = $request->get('bingo_id');
        $bingos = Bingo::orderBy('name')->get();

        // Estatísticas gerais
        $generalStats = [
            'total_bingos' => Bingo::count(),
            'total_cards' => Card::count(),
            'total_responsibles' => Responsible::count(),
            'total_winners' => Winner::count(),
            'total_numbers_drawn' => DrawnNumber::count(),
        ];

        // Cards por bingo
        $cardsByBingo = Bingo::withCount('cards')
            ->orderBy('cards_count', 'desc')
            ->take(10)
            ->get();

        // Cards por responsável
        $cardsByResponsible = Responsible::withCount('cards')
            ->where('status', 'active')
            ->orderBy('cards_count', 'desc')
            ->take(10)
            ->get();

        // Bingos finalizados com ganhadores
        $finishedBingos = Bingo::where('status', 'finished')
            ->withCount(['winners', 'cards'])
            ->orderBy('event_date', 'desc')
            ->take(10)
            ->get();

        // Últimos sorteios (números sorteados por bingo)
        $recentDraws = Bingo::whereIn('status', ['ongoing', 'finished'])
            ->withCount('drawnNumbers')
            ->with(['drawnNumbers' => function($q) {
                $q->orderBy('drawn_at', 'desc')->take(5);
            }])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Status das cartelas
        $cardStatusStats = [
            'available' => Card::where('status', 'available')->count(),
            'distributed' => Card::where('status', 'distributed')->count(),
            'returned' => Card::where('status', 'returned')->count(),
        ];

        // Se filtrar por bingo específico
        $bingoReport = null;
        if ($bingoId) {
            $bingoReport = Bingo::with(['prizePatterns', 'winners.card', 'winners.responsible'])
                ->withCount(['cards', 'drawnNumbers'])
                ->find($bingoId);
        }

        return view('pages.reports.index', compact(
            'bingos',
            'generalStats',
            'cardsByBingo',
            'cardsByResponsible',
            'finishedBingos',
            'recentDraws',
            'cardStatusStats',
            'bingoReport',
            'bingoId'
        ));
    }
}
