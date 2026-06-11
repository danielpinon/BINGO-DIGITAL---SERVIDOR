<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\Card;
use App\Models\Responsible;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stats = [
            'total_bingos' => Bingo::count(),
            'total_cards' => Card::count(),
            'total_responsibles' => Responsible::count(),
            'finished_bingos' => Bingo::where('status', 'finished')->count(),
        ];

        $ongoingBingos = Bingo::withCount('cards')
            ->whereIn('status', ['preparation', 'ongoing'])
            ->orderBy('event_date', 'desc')
            ->take(5)
            ->get();

        $recentBingos = Bingo::withCount('cards')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $cardsByResponsible = Responsible::withCount('cards')
            ->where('status', 'active')
            ->orderBy('cards_count', 'desc')
            ->take(6)
            ->get();

        $lastDrawnNumbers = [];
        $activeBingo = Bingo::with('rounds')->where('status', 'ongoing')->first();
        if ($activeBingo && $activeBingo->activeRound) {
            $lastDrawnNumbers = $activeBingo->activeRound->drawnNumbers()
                ->orderBy('drawn_at', 'desc')
                ->take(20)
                ->pluck('number')
                ->toArray();
        }

        return view('pages.dashboard', compact(
            'stats', 'ongoingBingos', 'recentBingos', 
            'cardsByResponsible', 'activeBingo', 'lastDrawnNumbers'
        ));
    }
}
