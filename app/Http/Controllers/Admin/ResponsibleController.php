<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Responsible;
use Illuminate\Http\Request;

class ResponsibleController extends Controller
{
    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        return $digits !== '' ? $digits : null;
    }

    public function index()
    {
        $responsibles = Responsible::withCount('cards')->orderBy('name')->paginate(10);
        
        $stats = [
            'total' => Responsible::count(),
            'active' => Responsible::where('status', 'active')->count(),
            'total_cards' => Responsible::withCount('cards')->get()->sum('cards_count'),
        ];
        
        return view('pages.responsibles.index', compact('responsibles', 'stats'));
    }

    public function create()
    {
        return view('pages.responsibles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['phone'] = $this->normalizePhone($validated['phone'] ?? null);
        Responsible::create($validated);

        return redirect()->route('responsibles.index')->with('sucesso', 'Responsável cadastrado com sucesso!');
    }

    public function show(Responsible $responsible)
    {
        $responsible->load('cards.bingo');
        return view('pages.responsibles.show', compact('responsible'));
    }

    public function edit(Responsible $responsible)
    {
        return view('pages.responsibles.edit', compact('responsible'));
    }

    public function update(Request $request, Responsible $responsible)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['phone'] = $this->normalizePhone($validated['phone'] ?? null);
        $responsible->update($validated);

        return redirect()->route('responsibles.index')->with('sucesso', 'Responsável atualizado com sucesso!');
    }

    public function destroy(Responsible $responsible)
    {
        $responsible->delete();
        return redirect()->route('responsibles.index')->with('sucesso', 'Responsável removido com sucesso!');
    }
}
