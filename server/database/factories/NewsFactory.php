<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Türkçe haber başlıkları
        $turkishTitles = [
            'Teknoloji Dünyasında Yeni Gelişmeler',
            'Ekonomik Büyüme Rakamları Açıklandı',
            'Spor Dünyasından Son Haberler',
            'Sağlık Sektöründe Önemli Adımlar',
            'Eğitim Reformu Kapsamında Yeni Düzenlemeler',
            'Çevre Koruma Projeleri Hayata Geçiyor',
            'Turizm Sektöründe Rekor Artış',
            'Bilim İnsanlarından Çığır Açan Keşif',
            'Kültür ve Sanat Etkinlikleri Devam Ediyor',
            'Ulaşım Projeleri Hızla İlerliyor'
        ];

        // Türkçe içerik paragrafları
        $turkishContent = [
            'Bu gelişme, sektörde önemli değişikliklere yol açması bekleniyor. Uzmanlar konuyla ilgili olumlu görüşlerini paylaştı.',
            'Yapılan araştırmalar sonucunda elde edilen veriler, gelecek dönem için umut verici sonuçlar ortaya koyuyor.',
            'İlgili kurumlar tarafından yapılan açıklamalarda, projenin başarıyla tamamlanacağı belirtildi.',
            'Vatandaşların büyük ilgi gösterdiği bu konu, sosyal medyada da geniş yankı uyandırdı.',
            'Konunun detayları hakkında yapılan briefingde, tüm sorular yanıtlandı ve net bilgiler paylaşıldı.'
        ];

        $title = fake()->randomElement($turkishTitles) . ' - ' . fake()->words(2, true);
        $content = fake()->randomElements($turkishContent, 3);
        $contentText = implode(' ', $content) . ' ' . fake()->paragraph(3);

        // Benzersiz slug oluştur
        $baseSlug = \Illuminate\Support\Str::slug($title);
        $uniqueSlug = $baseSlug . '-' . time() . '-' . fake()->randomNumber(4);

        return [
            'id' => fake()->uuid(),
            'title' => $title,
            'content' => $contentText,
            'slug' => $uniqueSlug,
            'image_path' => 'news-images/2025/06/24/effcaabe-bd98-463d-a40f-8b4e66b608a0.webp',
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'published_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }
}
