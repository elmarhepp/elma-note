<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'notebook_id', 'title', 'content',
        'content_json', 'is_favorite', 'is_pinned',
    ];

    protected $casts = [
        'content_json' => 'array',
        'is_favorite'  => 'boolean',
        'is_pinned'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['title', 'content'], $term);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }
}
