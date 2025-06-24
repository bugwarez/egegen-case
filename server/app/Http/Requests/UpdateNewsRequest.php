<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Haber güncelleme isteği için validasyon sınıfı
 *
 * Bu sınıf mevcut haber güncellenirken gelen verilerin validasyonunu yapar
 * ve Türkçe hata mesajları döndürür
 */
class UpdateNewsRequest extends FormRequest
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
        // Güncellenecek haberin ID'sini route'dan alıyoruz
        $newsId = $this->route('news');

        return [
            // Haber başlığı kuralları (güncelleme için opsiyonel)
            'title' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[\p{L}\p{N}\s\-\.\,\!\?\:\;\"\']+$/u', // Türkçe karakterler ve temel noktalama
            ],

            // Haber içeriği kuralları (güncelleme için opsiyonel)
            'content' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:65535', // TEXT sütunu limiti
            ],

            // SEO slug kuralları (mevcut kaydı hariç unique)
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/', // Sadece küçük harf, rakam ve tire
                Rule::unique('news', 'slug')->ignore($newsId),
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
                // Güncelleme sırasında geçmiş tarih de olabilir (zaten yayınlanmış haberler için)
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
            'slug.unique' => 'Bu URL slug zaten başka bir haber tarafından kullanılıyor.',

            // Görsel hata mesajları
            'image.image' => 'Yüklenen dosya geçerli bir görsel olmalıdır.',
            'image.mimes' => 'Görsel JPEG, JPG, PNG veya WebP formatında olmalıdır.',
            'image.max' => 'Görsel boyutu en fazla :max KB olabilir.',
            'image.dimensions' => 'Görsel boyutları geçersiz (min: 100x100px, max: 800x800px).',

            // Durum hata mesajları
            'status.in' => 'Haber durumu geçersiz. Seçenekler: draft, published, archived',

            // Tarih hata mesajları
            'published_at.date' => 'Yayınlanma tarihi geçerli bir tarih olmalıdır.',
        ];
    }

    /**
     * Validasyon öncesi veri hazırlama
     *
     * Gelen verileri validasyon için uygun formata getirir
     */
    protected function prepareForValidation(): void
    {
        // Başlık temizleme (sadece gönderilmişse)
        if ($this->has('title')) {
            $this->merge([
                'title' => trim(strip_tags($this->input('title'))),
            ]);
        }

        // İçerik temizleme (sadece gönderilmişse)
        if ($this->has('content')) {
            $this->merge([
                'content' => trim($this->input('content')),
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

    /**
     * Sadece değişen alanları döndür (PATCH istekleri için)
     *
     * @return array
     */
    public function validatedOnly(): array
    {
        return $this->only([
            'title',
            'content',
            'slug',
            'status',
            'published_at'
        ]);
    }
}
