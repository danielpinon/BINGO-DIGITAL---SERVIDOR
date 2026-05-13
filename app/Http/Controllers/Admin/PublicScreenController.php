<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;

class PublicScreenController extends Controller
{
    public function show(Bingo $bingo)
    {
        $bingo->load(['drawnNumbers', 'winners.card', 'winners.responsible', 'winners.prizePattern']);
        return view('pages.public.screen', compact('bingo'));
    }
}
