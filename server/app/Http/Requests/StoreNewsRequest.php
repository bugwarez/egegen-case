<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Yeni haber ekleme isteği için validasyon sınıfı
 *
 * Bu sınıf yeni haber eklenirken gelen verilerin validasyonunu yapar
 * ve Türkçe hata mesajları döndürür
 */
class StoreNewsRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkili olup olmadığını belirler
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // API için bearer token middleware'ı yetkilendirmeyi halleder
        // Bu nedenle burada true döndürüyoruz
        return true;
    }

    /**
     * İstekte uygulanacak validasyon kurallarını döndürür
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Haber başlığı kuralları
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[\p{L}\p{N}\s\-\.\,\!\?\:\;\"\']+$/u', // Türkçe karakterler ve temel noktalama
            ],

            // Haber içeriği kuralları
            'content' => [
                'required',
                'string',
                'min:10',
                'max:65535', // TEXT sütunu limiti
            ],

            // SEO slug kuralları (otomatik oluşturulur)
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/', // Sadece küçük harf, rakam ve tire
                'unique:news,slug',
            ],

            // Haber görseli kuralları
            'image' => [
                'sometimes',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB maksimum
                // Boyut kontrolü ImageProcessingService'de yapılacak (otomatik resize)
            ],

            // Haber durumu kuralları
            'status' => [
                'sometimes',
                Rule::in(['draft', 'published', 'archived']),
            ],

            // Yayınlanma tarihi kuralları
            'published_at' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:now', // Geçmiş tarih olamaz
            ],
        ];
    }

    /**
     * Validasyon hatalarında döndürülecek Türkçe mesajlar
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Başlık hata mesajları
            'title.required' => 'Haber başlığı zorunludur.',
            'title.string' => 'Haber başlığı metin formatında olmalıdır.',
            'title.min' => 'Haber başlığı en az :min karakter olmalıdır.',
            'title.max' => 'Haber başlığı en fazla :max karakter olabilir.',
            'title.regex' => 'Haber başlığı geçersiz karakterler içeriyor.',

            // İçerik hata mesajları
            'content.required' => 'Haber içeriği zorunludur.',
            'content.string' => 'Haber içeriği metin formatında olmalıdır.',
            'content.min' => 'Haber içeriği en az :min karakter olmalıdır.',
            'content.max' => 'Haber içeriği çok uzun.',

            // Slug hata mesajları
            'slug.string' => 'URL slug metin formatında olmalıdır.',
            'slug.max' => 'URL slug en fazla :max karakter olabilir.',
            'slug.regex' => 'URL slug sadece küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu URL slug zaten kullanılıyor.',

            // Görsel hata mesajları
            'image.image' => 'Yüklenen dosya geçerli bir görsel olmalıdır.',
            'image.mimes' => 'Görsel JPEG, JPG, PNG veya WebP formatında olmalıdır.',
            'image.max' => 'Görsel boyutu en fazla :max KB olabilir.',
            'image.dimensions' => 'Görsel boyutları geçersiz (min: 100x100px, max: 800x800px).',

            // Durum hata mesajları
            'status.in' => 'Haber durumu geçersiz. (draft, published, archived)',

            // Tarih hata mesajları
            'published_at.date' => 'Yayınlanma tarihi geçerli bir tarih olmalıdır.',
            'published_at.after_or_equal' => 'Yayınlanma tarihi geçmiş olamaz.',
        ];
    }

    /**
     * Validasyon öncesi veri hazırlama
     *
     * Gelen verileri validasyon için uygun formata getirir
     */
    protected function prepareForValidation(): void
    {
        // Başlık ve içerik temizleme
        if ($this->has('title')) {
            $this->merge([
                'title' => trim(strip_tags($this->input('title'))),
            ]);
        }

        if ($this->has('content')) {
            $this->merge([
                'content' => trim($this->input('content')),
            ]);
        }

        // Durum varsayılan değeri
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'draft',
            ]);
        }
    }

    /**
     * Validasyon geçtikten sonra döndürülecek alan isimleri
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'başlık',
            'content' => 'içerik',
            'slug' => 'URL slug',
            'image' => 'görsel',
            'status' => 'durum',
            'published_at' => 'yayınlanma tarihi',
        ];
    }
}
