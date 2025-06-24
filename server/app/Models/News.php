<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class News extends Model
{
    /** @use HasFactory<\Database\Factories\NewsFactory> */
    use HasFactory;

    /**
     * UUID primary key kullanımı için Laravel'e bildiriyoruz
     */
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'content',
        'slug',
        'image_path',
        'status',
        'published_at',
    ];


    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    protected static function boot(): void
    {
        parent::boot();

        // Haber oluşturulurken UUID ve slug otomatik oluşturuluyor
        static::creating(function ($news) {
            // UUID otomatik oluştur
            if (empty($news->id)) {
                $news->id = Str::uuid()->toString();
            }

            // Slug otomatik oluştur
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);

                // Slug benzersiz olacak şekilde oluşturuluyor
                $originalSlug = $news->slug;
                $counter = 1;
                while (static::where('slug', $news->slug)->exists()) {
                    $news->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        // Başlık değiştiğinde slug otomatik güncelleniyor
        static::updating(function ($news) {
            if ($news->isDirty('title') && !$news->isDirty('slug')) {
                $news->slug = Str::slug($news->title);

                // Slug benzersiz olacak şekilde oluşturuluyor (şu anki kayıt hariç)
                $originalSlug = $news->slug;
                $counter = 1;
                while (static::where('slug', $news->slug)->where('id', '!=', $news->id)->exists()) {
                    $news->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    /**
     * Yayınlanmış haberler için scope
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Haberleri aramak için scope
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('content', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Haber görselinin tam URL'sini almak için
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * Haberin yayınlanmış olup olmadığını kontrol etmek için
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
               && $this->published_at !== null
               && $this->published_at <= now();
    }

    /**
     * Haber özetini döndürün (content'in ilk 150 karakteri)
     */
    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 150);
    }
}
