<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;

class DrawController extends Controller
{
    public function index(Bingo $bingo)
    {
        if ($bingo->status === 'preparation') {
            return redirect()->route('bingos.index')->with('falha', 'O bingo ainda não foi iniciado.');
        }

        $bingo->load(['prizePatterns', 'drawnNumbers', 'cards.responsible', 'winners']);
        
        return view('pages.draw.index', compact('bingo'));
    }
}
