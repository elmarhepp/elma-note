<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotebookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('notes.index'));

Route::middleware(['auth'])->group(function () {
    // Notes
    Route::resource('notes', NoteController::class)->except(['edit']);
    Route::get('/trash', [NoteController::class, 'trash'])->name('notes.trash');
    Route::patch('/notes/{id}/restore', [NoteController::class, 'restore'])->name('notes.restore');
    Route::delete('/notes/{id}/force', [NoteController::class, 'forceDelete'])->name('notes.force-delete');

    // Notebooks
    Route::resource('notebooks', NotebookController::class)->only(['index', 'store', 'update', 'destroy']);

    // Tags
    Route::resource('tags', TagController::class)->only(['store', 'update', 'destroy']);

    // Profile (von Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
