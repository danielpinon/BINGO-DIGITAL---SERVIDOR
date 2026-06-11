<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WinnerController extends Controller
{
    public function index()
    {
        $winners = Winner::with(['bingo', 'round', 'card', 'prizePattern', 'responsible'])
            ->orderBy('confirmed_at', 'desc')
            ->paginate(20);
        
        return view('pages.winners.index', compact('winners'));
    }

    public function confirm(Request $request, Winner $winner)
    {
        $winner->update([
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        return back()->with('sucesso', 'Ganhador confirmado com sucesso!');
    }
}
