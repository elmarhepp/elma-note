<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Auth::user()->notes()->with(['notebook', 'tags'])->latest('updated_at');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('notebook')) {
            $query->where('notebook_id', $request->notebook);
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->tag));
        }

        if ($request->boolean('favorites')) {
            $query->favorites();
        }

        $notes = $query->paginate(50)->withQueryString();

        return Inertia::render('Notes/Index', [
            'notes'     => $notes,
            'notebooks' => Auth::user()->notebooks()->withCount('notes')->orderBy('name')->get(),
            'tags'      => Auth::user()->tags()->withCount('notes')->orderBy('name')->get(),
            'filters'   => $request->only(['search', 'notebook', 'tag', 'favorites']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Notes/Create', [
            'notebooks' => Auth::user()->notebooks()->withCount('notes')->orderBy('name')->get(),
            'tags'      => Auth::user()->tags()->withCount('notes')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'nullable|string',
            'content_json'=> 'nullable|array',
            'notebook_id' => 'nullable|exists:notebooks,id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,id',
            'is_favorite' => 'boolean',
            'is_pinned'   => 'boolean',
        ]);

        $note = Auth::user()->notes()->create($validated);

        if (!empty($validated['tags'])) {
            $note->tags()->sync($validated['tags']);
        }

        return redirect()->route('notes.show', $note)->with('success', 'Notiz erstellt.');
    }

    public function show(Note $note): Response
    {
        $this->authorize('view', $note);

        $note->load(['notebook', 'tags']);

        return Inertia::render('Notes/Show', [
            'note'      => $note,
            'notebooks' => Auth::user()->notebooks()->withCount('notes')->orderBy('name')->get(),
            'tags'      => Auth::user()->tags()->withCount('notes')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'content'      => 'nullable|string',
            'content_json' => 'nullable|array',
            'notebook_id'  => 'nullable|exists:notebooks,id',
            'tags'         => 'nullable|array',
            'tags.*'       => 'exists:tags,id',
            'is_favorite'  => 'sometimes|boolean',
            'is_pinned'    => 'sometimes|boolean',
        ]);

        $note->update($validated);

        if (array_key_exists('tags', $validated)) {
            $note->tags()->sync($validated['tags'] ?? []);
        }

        return back()->with('success', 'Notiz gespeichert.');
    }

    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);
        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Notiz in den Papierkorb verschoben.');
    }

    public function trash(): Response
    {
        $notes = Auth::user()->notes()->onlyTrashed()->latest('deleted_at')->paginate(50);

        return Inertia::render('Notes/Trash', ['notes' => $notes]);
    }

    public function restore(int $id)
    {
        $note = Auth::user()->notes()->onlyTrashed()->findOrFail($id);
        $note->restore();

        return back()->with('success', 'Notiz wiederhergestellt.');
    }

    public function forceDelete(int $id)
    {
        $note = Auth::user()->notes()->onlyTrashed()->findOrFail($id);
        $note->forceDelete();

        return back()->with('success', 'Notiz endgültig gelöscht.');
    }
}
