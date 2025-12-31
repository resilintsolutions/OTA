<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Hotel extends Model
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'city',
        'vendor',
        'vendor_id',
        'lowest_rate',
        'highest_rate',
        'margin_inc',
        'currency',
        'description',
        'status',
        'meta',
        'country_iso',
        'destination_code',
        'destination_name',
        'address',
        'hotel_email',
        'hotel_phones',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'lowest_rate' => 'float',
        'margin_inc'  => 'float',
        'hotel_phones'=> 'array',
        'meta' => 'array',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function media()
    {
    // Compat: this project currently has the Spatie-style media schema
    // (model_type/model_id/collection_name) and uses our own Media model.
    // So we morph on `model` (not `mediable`).
    return $this->morphMany(\App\Models\Media::class, 'model');
    }

    public function images()
    {
        // Spatie schema uses `collection_name`; our custom schema uses `collection`.
        // Prefer `collection_name` if present.
        $relation = $this->media();

        if (\Illuminate\Support\Facades\Schema::hasColumn('media', 'collection_name')) {
            return $relation->where('collection_name', 'images')->orderBy('order_column');
        }

        return $relation->where('collection', 'images')->orderBy('position');
    }

    public function getFeaturedImageAttribute()
    {
        $isSpatie = \Illuminate\Support\Facades\Schema::hasColumn('media', 'collection_name');
        $collectionCol = $isSpatie ? 'collection_name' : 'collection';
        $posCol = $isSpatie ? 'order_column' : 'position';

        $media = $this->media
            ->where($collectionCol, 'images')
            ->sortBy($posCol)
            ->firstWhere('is_featured', true);

        // fallback: first image
        if (!$media) {
            $media = $this->media
                ->where($collectionCol, 'images')
                ->sortBy($posCol)
                ->first();
        }

        if (!$media) {
            return null;
        }

        // Prefer external URL, else local
        // Prefer external URL, else local
        if (!empty($media->external_url)) {
            return $media->external_url;
        }

        // Spatie schema stores file name under `file_name` and expects a conversions system;
        // our custom Media model exposes `url` for a best-effort public URL.
        return $media->url;
    }

    public function getImagesAttribute()
    {
        $isSpatie = \Illuminate\Support\Facades\Schema::hasColumn('media', 'collection_name');
        $collectionCol = $isSpatie ? 'collection_name' : 'collection';
        $posCol = $isSpatie ? 'order_column' : 'position';

        return $this->media
            ->where($collectionCol, 'images')
            ->sortBy($posCol)
            ->map(fn ($m) => $m->url)
            ->values()
            ->all();
    }

    // in Hotel model
    public function getDisplayLowestRateAttribute(): ?float
    {
        if (is_null($this->lowest_rate)) {
            return null;
        }

        // assuming margin_inc is percent, e.g. 15 = +15%
        $marginMultiplier = 1 + (($this->margin_inc ?? 0) / 100);

        return round($this->lowest_rate * $marginMultiplier, 2);
    }

    public function exclusion()
    {
        return $this->hasOne(\App\Models\HotelExclusion::class);
    }


}
