<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $tag = Auth::user()->tags()->firstOrCreate(
            ['name' => $validated['name']],
            ['color' => $validated['color'] ?? '#6366f1'],
        );

        return back()->with('success', "Tag \"{$tag->name}\" erstellt.");
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $validated = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $tag->update($validated);

        return back()->with('success', 'Tag aktualisiert.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);
        $tag->delete();

        return back()->with('success', 'Tag gelöscht.');
    }
}
