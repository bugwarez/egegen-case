<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
// use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Görsel İşleme Servisi
 *
 * Haber görsellerini yükler, WebP formatına dönüştürür
 * ve maksimum 800px boyutunda yeniden boyutlandırır
 */
class ImageProcessingService
{
    /**
     * Maksimum görsel boyutu (px)
     */
    private const MAX_DIMENSION = 800;

    /**
     * Görsel kalitesi (WebP için)
     */
    private const WEBP_QUALITY = 85;

    /**
     * Desteklenen görsel formatları
     */
    private const SUPPORTED_FORMATS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct()
    {
        // GD extension kontrolü - eğer yoksa fallback kullan
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension gerekli ancak yüklü değil.');
        }
    }

    /**
     * Görseli işle ve kaydet
     *
     * @param UploadedFile $file
     * @param string|null $oldImagePath Eski görsel yolu (güncelleme için)
     * @return string İşlenmiş görselin yolu
     * @throws \Exception
     */
    public function processAndStore(UploadedFile $file, ?string $oldImagePath = null): string
    {
        try {
            // Dosya formatını kontrol et
            $this->validateImageFile($file);

            // Eski görseli sil (güncelleme durumunda)
            if ($oldImagePath) {
                $this->deleteImage($oldImagePath);
            }

            // Benzersiz dosya adı oluştur (her zaman .webp uzantısı ile)
            $filename = $this->generateUniqueFilename() . '.webp';

            // Önce image fonksiyonlarının çalışıp çalışmadığını test et
            if ($this->canProcessImages()) {
                return $this->processAndResize($file, $filename);
            } else {
                // Basit dosya yükleme
                return $this->simpleFileUpload($file, $filename);
            }

        } catch (\Exception $e) {
            throw new \Exception('Görsel işlenirken hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Dosyanın geçerli bir görsel olup olmadığını kontrol et
     */
    private function validateImageFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, self::SUPPORTED_FORMATS)) {
            throw new \Exception('Desteklenmeyen dosya formatı. Desteklenen formatlar: ' . implode(', ', self::SUPPORTED_FORMATS));
        }

        // MIME type kontrolü
        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Geçersiz dosya türü.');
        }
    }

    /**
     * Görseli yeniden boyutlandır ve WebP formatında kaydet
     */
    private function processAndResize(UploadedFile $file, string $filename): string
    {
        // Orijinal görseli yükle
        $sourceImage = $this->createImageFromFile($file);
        if (!$sourceImage) {
            throw new \Exception('Görsel dosyası okunamadı.');
        }

                // Orijinal boyutları al
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        // Yeni boyutları hesapla (800x800px sabit)
        $newDimensions = $this->calculateNewDimensions($originalWidth, $originalHeight);

        // Yeni görsel oluştur (800x800px)
        $resizedImage = imagecreatetruecolor($newDimensions['width'], $newDimensions['height']);

        // Şeffaflık desteği (PNG için)
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);

        // Crop işlemi için merkez noktasını hesapla
        $cropData = $this->calculateCropArea($originalWidth, $originalHeight);

        // Görseli crop et ve 800x800px'e sığdır
        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0, 0,                                    // Hedef koordinatları (0,0)
            $cropData['x'], $cropData['y'],          // Kaynak crop başlangıç noktası
            $newDimensions['width'], $newDimensions['height'], // Hedef boyutlar (800x800)
            $cropData['width'], $cropData['height']  // Kaynak crop boyutları
        );

        // Dosya yolunu oluştur
        $directory = 'news-images';
        $path = $directory . '/' . $filename;
        $fullPath = storage_path('app/public/' . $path);

        // Dizini oluştur
        $this->ensureDirectoryExists(dirname($fullPath));

        // WebP formatında kaydet
        if (!imagewebp($resizedImage, $fullPath, self::WEBP_QUALITY)) {
            throw new \Exception('Görsel WebP formatında kaydedilemedi.');
        }

        // Belleği temizle
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $path;
    }

    /**
     * Dosyadan görsel oluştur
     */
    private function createImageFromFile(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();
        $filePath = $file->getPathname();

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }

    /**
     * Yeni boyutları hesapla (800x800px sabit boyut)
     */
    private function calculateNewDimensions(int $originalWidth, int $originalHeight): array
    {
        // Her zaman 800x800px boyutunda crop et
        return [
            'width' => self::MAX_DIMENSION,
            'height' => self::MAX_DIMENSION
        ];
    }

    /**
     * Crop alanını hesapla (merkez odaklı kare crop)
     */
    private function calculateCropArea(int $originalWidth, int $originalHeight): array
    {
        // En küçük boyutu bul (kare crop için)
        $cropSize = min($originalWidth, $originalHeight);

        // Merkez noktasından crop başlangıç koordinatlarını hesapla
        $cropX = ($originalWidth - $cropSize) / 2;
        $cropY = ($originalHeight - $cropSize) / 2;

        return [
            'x' => (int) $cropX,
            'y' => (int) $cropY,
            'width' => $cropSize,
            'height' => $cropSize
        ];
    }

    /**
     * Basit dosya yükleme (GD extension yoksa)
     */
    private function simpleFileUpload(UploadedFile $file, string $filename): string
    {
        $directory = 'news-images';
        $extension = strtolower($file->getClientOriginalExtension());

        // WebP olmayan dosya adını düzelt
        $actualFilename = str_replace('.webp', '.' . $extension, $filename);
        $path = $directory . '/' . $actualFilename;

        // Dosyayı kaydet
        $file->storeAs('public/' . $directory, $actualFilename);

        \Log::warning("GD extension bulunamadı. Görsel işleme yapılamıyor. Dosya: {$actualFilename}");

        return $path;
    }

    /**
     * Dizinin var olduğundan emin ol
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Benzersiz dosya adı oluştur
     *
     * @return string
     */
    private function generateUniqueFilename(): string
    {
        return date('Y/m/d') . '/' . Str::uuid();
    }

    /**
     * Görseli sil
     *
     * @param string $imagePath
     * @return bool
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            $fullPath = storage_path('app/public/' . $imagePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Görsel silinirken hata: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Görselin tam URL'sini al
     *
     * @param string|null $imagePath
     * @return string|null
     */
    public function getImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        return asset('storage/' . $imagePath);
    }

    /**
     * Görselin varlığını kontrol et
     *
     * @param string|null $imagePath
     * @return bool
     */
    public function imageExists(?string $imagePath): bool
    {
        if (!$imagePath) {
            return false;
        }

        return file_exists(storage_path('app/public/' . $imagePath));
    }

    /**
     * Görsel bilgilerini al
     *
     * @param string $imagePath
     * @return array|null
     */
    public function getImageInfo(string $imagePath): ?array
    {
        try {
            $fullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($fullPath)) {
                return null;
            }

            // Temel dosya bilgileri
            $fileSize = filesize($fullPath);
            $mimeType = mime_content_type($fullPath);

            // Görsel boyutlarını al (getimagesize ile)
            $imageInfo = getimagesize($fullPath);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            return [
                'width' => $width,
                'height' => $height,
                'size' => $fileSize,
                'mime_type' => $mimeType,
                'url' => $this->getImageUrl($imagePath),
                'path' => $imagePath,
                'exists' => true
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Image processing fonksiyonlarının çalışıp çalışmadığını test et
     */
    private function canProcessImages(): bool
    {
        // Gerekli fonksiyonları test et
        return function_exists('imagecreatefromjpeg') &&
               function_exists('imagecreatetruecolor') &&
               function_exists('imagecopyresampled') &&
               function_exists('imagewebp');
    }
}
