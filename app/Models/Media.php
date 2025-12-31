<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
    // Custom schema
    'mediable_type',
    'mediable_id',
    'path',
    'collection',
    'position',

    // Spatie schema
    'model_type',
    'model_id',
    'collection_name',
    'order_column',
    'name',
    'disk',
    'custom_properties',

    // Shared-ish
    'file_name',
    'mime_type',
    'size',
    'external_url',
    'meta',
    'is_featured',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
    'custom_properties' => 'array',
    ];

    public function mediable()
    {
        // Backwards-compatible alias for our custom schema.
        return $this->morphTo();
    }

    public function model()
    {
        // Spatie-compatible morph.
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        // If external URL exists (Hotelbeds), use it
        if (!empty($this->external_url)) {
            return $this->external_url;
        }

        // Otherwise return local storage path
        // Prefer our custom `path` column if present.
        if (!empty($this->path)) {
            return Storage::disk('public')->url($this->path);
        }

        // Spatie-style: best-effort URL (requires the file to exist on the disk).
        if (!empty($this->file_name) && !empty($this->disk)) {
            // Spatie default location is usually `${id}/${file_name}`.
            // This is only to keep admin pages from crashing in dev.
            return Storage::disk($this->disk)->url($this->id.'/'.$this->file_name);
        }

        return null;
    }

}
