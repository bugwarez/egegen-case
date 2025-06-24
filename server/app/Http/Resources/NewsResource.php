<?php

namespace App\Http\Resources;

use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Haber API Resource'u
 *
 * Bu resource haberlerin API response formatını belirler
 * ve tutarlı JSON yapısı sağlar
 */
class NewsResource extends JsonResource
{
    /**
     * Resource'u array formatına dönüştürür
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Temel haber bilgileri
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,

            // Görsel bilgileri
            'image' => $this->getImageInfo(),

            // Durum ve yayınlanma bilgileri
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'published_at_human' => $this->published_at?->diffForHumans(),
            'is_published' => $this->isPublished(),

            // Tarih bilgileri
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'updated_at_human' => $this->updated_at->diffForHumans(),

            // URL ve SEO
            'urls' => [
                'api' => route('api.news.show', $this->id),
                'slug' => route('api.news.slug', $this->slug),
            ],
        ];
    }

    /**
     * Durum etiketini Türkçe olarak döndürür
     *
     * @return string
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Taslak',
            'published' => 'Yayınlandı',
            'archived' => 'Arşivlendi',
            default => 'Bilinmiyor',
        };
    }

    /**
     * Görsel bilgilerini detaylı olarak döndürür
     *
     * @return array
     */
    private function getImageInfo(): array
    {
        if (!$this->image_path) {
            return [
                'path' => null,
                'url' => null,
                'exists' => false,
                'width' => null,
                'height' => null,
                'size' => null,
                'mime_type' => null,
            ];
        }

        $imageService = app(ImageProcessingService::class);
        $imageInfo = $imageService->getImageInfo($this->image_path);

        return $imageInfo ?: [
            'path' => $this->image_path,
            'url' => $this->image_url,
            'exists' => false,
            'width' => null,
            'height' => null,
            'size' => null,
            'mime_type' => null,
        ];
    }

    /**
     * Resource collection için ek meta data
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'response_time' => now()->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * Resource wrapper'ını özelleştir
     *
     * @param array $resource
     * @param array $default
     * @return array
     */
    public static function wrap($resource, $default = 'data'): array
    {
        return ['data' => $resource];
    }
}
