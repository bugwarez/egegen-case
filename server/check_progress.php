<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$newsCount = App\Models\News::count();
$target = 250000;
$percentage = round(($newsCount / $target) * 100, 2);

echo "📰 Haber Oluşturma İlerlemesi\n";
echo "================================\n";
echo "Mevcut: {$newsCount} / {$target}\n";
echo "İlerleme: {$percentage}%\n";
echo "Kalan: " . ($target - $newsCount) . "\n";

if ($newsCount >= $target) {
    echo "✅ Seeding tamamlandı!\n";

    // İstatistikleri göster
    $published = App\Models\News::where('status', 'published')->count();
    $draft = App\Models\News::where('status', 'draft')->count();
    $archived = App\Models\News::where('status', 'archived')->count();

    echo "\n📊 Durum İstatistikleri:\n";
    echo "Yayınlanan: {$published}\n";
    echo "Taslak: {$draft}\n";
    echo "Arşivlenen: {$archived}\n";
} else {
    echo "⏳ Seeding devam ediyor...\n";
}
