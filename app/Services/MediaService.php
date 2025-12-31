<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Hotel;

class MediaService
{
    /**
     * Download an external URL and store under storage/app/public/hotels/{hotelId}/...
     * Returns Media model on success or null.
     */
    public function importForHotel(int $hotelId, string $externalUrl, string $collection = 'images', array $meta = []): ?Media
    {
        try {
            // simple HEAD to see mime
            $resp = Http::withOptions(['verify' => true])->get($externalUrl);
            if (! $resp->ok()) return null;

            $content = $resp->body();
            $mime = $resp->header('Content-Type') ?: 'application/octet-stream';
            $ext = $this->extensionFromMime($mime) ?: pathinfo(parse_url($externalUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

            $filename = Str::slug(pathinfo(parse_url($externalUrl, PHP_URL_PATH), PATHINFO_FILENAME) ?: 'img') . '-' . Str::random(6) . '.' . $ext;
            $folder = "hotels/{$hotelId}/{$collection}";
            $path = "$folder/$filename";

            Storage::disk('public')->put($path, $content);

            $isSpatie = \Illuminate\Support\Facades\Schema::hasColumn('media', 'collection_name');

            // Next order within this collection for this hotel.
            $order = null;
            if ($isSpatie && \Illuminate\Support\Facades\Schema::hasColumn('media', 'order_column')) {
                $order = (int) Media::query()
                    ->where('model_type', Hotel::class)
                    ->where('model_id', $hotelId)
                    ->where('collection_name', $collection)
                    ->max('order_column');
                $order = $order ? ($order + 1) : 1;
            }

            $payload = [
                // Shared-ish
                'file_name'    => $filename,
                'mime_type'    => $mime,
                'size'         => strlen($content),
                'external_url' => $externalUrl,
                'meta'         => $meta,
            ];

            if ($isSpatie) {
                // Spatie schema present in DB.
                $payload += [
                    'model_type'       => Hotel::class,
                    'model_id'         => $hotelId,
                    'collection_name'  => $collection,
                    'name'             => pathinfo($filename, PATHINFO_FILENAME),
                    'disk'             => 'public',
                    'custom_properties'=> ['path' => $path] + ($meta ?? []),
                ];

                if ($order !== null) {
                    $payload['order_column'] = $order;
                }
            } else {
                // Legacy custom schema.
                $payload += [
                    'mediable_type' => Hotel::class,
                    'mediable_id'   => $hotelId,
                    'collection'    => $collection,
                    'path'          => $path,
                ];
            }

            $media = Media::create($payload);
            return $media;
        } catch (\Exception $e) {
            logger()->warning('Media import failed: ' . $e->getMessage(), ['url' => $externalUrl]);
            return null;
        }
    }

    protected function extensionFromMime($mime)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'video/mp4'  => 'mp4',
            'application/pdf' => 'pdf',
        ];
        return $map[$mime] ?? null;
    }
}
