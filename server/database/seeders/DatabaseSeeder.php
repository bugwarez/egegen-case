<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\News;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Veritabanı seed başlatılıyor...');

        // 250,000 haber kaydı oluştur (bulk insert ile hızlı)
        $this->command->info('250,000 haber oluşturuluyor...');

        $totalRecords = 250000;
        $chunkSize = 5000;
        $chunks = ceil($totalRecords / $chunkSize);

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

        $statuses = ['draft', 'published', 'archived'];
        $imagePath = 'news-images/2025/06/24/effcaabe-bd98-463d-a40f-8b4e66b608a0.webp';

        $progressBar = $this->command->getOutput()->createProgressBar($chunks);
        $progressBar->start();

        for ($chunk = 0; $chunk < $chunks; $chunk++) {
            $currentChunkSize = ($chunk === $chunks - 1) ? ($totalRecords % $chunkSize) ?: $chunkSize : $chunkSize;

            $records = [];

            for ($i = 0; $i < $currentChunkSize; $i++) {
                $title = $turkishTitles[array_rand($turkishTitles)] . ' - ' . fake()->words(2, true);
                $content = $turkishContent[array_rand($turkishContent)] . ' ' . $turkishContent[array_rand($turkishContent)];
                $createdAt = fake()->dateTimeBetween('-6 months', 'now');
                $updatedAt = fake()->dateTimeBetween($createdAt, 'now');
                $status = $statuses[array_rand($statuses)];

                $records[] = [
                    'id' => Str::uuid(),
                    'title' => $title,
                    'content' => $content,
                    'slug' => Str::slug($title) . '-' . time() . '-' . ($chunk * $chunkSize + $i),
                    'image_path' => $imagePath,
                    'status' => $status,
                    'published_at' => ($status === 'published' && rand(1, 100) <= 80) ? $createdAt : null,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            }

            // Bulk insert
            DB::table('news')->insert($records);

            $progressBar->advance();

            // Memory temizleme
            unset($records);
            if ($chunk % 5 === 0) {
                gc_collect_cycles();
            }
        }

        $progressBar->finish();
        $this->command->newLine();

        // Sonuçları göster
        $newsCount = News::count();
        $publishedCount = News::where('status', 'published')->count();
        $draftCount = News::where('status', 'draft')->count();
        $archivedCount = News::where('status', 'archived')->count();

        $this->command->info("✅ Seeding tamamlandı!");
        $this->command->info("📊 İstatistikler:");
        $this->command->info("   • Toplam haber: {$newsCount}");
        $this->command->info("   • Yayınlanan: {$publishedCount}");
        $this->command->info("   • Taslak: {$draftCount}");
        $this->command->info("   • Arşivlenen: {$archivedCount}");
        $this->command->info("🎉 Tüm haberler aynı görseli kullanıyor: {$imagePath}");
    }
}
