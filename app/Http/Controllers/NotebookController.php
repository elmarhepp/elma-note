<?php

namespace App\Http\Controllers;

use App\Models\Notebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotebookController extends Controller
{
    public function index(): Response
    {
        $notebooks = Auth::user()->notebooks()
            ->withCount('notes')
            ->orderBy('name')
            ->get();

        return Inertia::render('Notebooks/Index', ['notebooks' => $notebooks]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $notebook = Auth::user()->notebooks()->create($validated);

        return back()->with('success', "Notizbuch \"{$notebook->name}\" erstellt.");
    }

    public function update(Request $request, Notebook $notebook)
    {
        $this->authorize('update', $notebook);

        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $notebook->update($validated);

        return back()->with('success', 'Notizbuch aktualisiert.');
    }

    public function destroy(Notebook $notebook)
    {
        $this->authorize('delete', $notebook);
        $notebook->delete();

        return back()->with('success', 'Notizbuch gelöscht.');
    }
}
