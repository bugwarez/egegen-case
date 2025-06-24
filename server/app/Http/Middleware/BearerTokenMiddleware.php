<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer Token Middleware
 *
 * Bu middleware belirtilen bearer token'ı kontrol eder
 * ve geçersiz istekleri IP blacklist sistemi ile takip eder
 */
class BearerTokenMiddleware
{
    /**
     * İzin verilen bearer token (case study gereksinimi)
     */
    private const ALLOWED_TOKEN = '2BH52wAHrAymR7wP3CASt';

    /**
     * IP başına maksimum başarısız deneme sayısı
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * IP blacklist süresi (dakika)
     */
    private const BLACKLIST_DURATION = 10;

    /**
     * Gelen isteği işle
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();

        // IP blacklist durumunu kontrol et
        if ($this->isIpBlacklisted($clientIp)) {
            return response()->json([
                'success' => false,
                'message' => 'IP adresiniz geçici olarak engellenmiştir. 10 dakika sonra tekrar deneyin.',
                'error' => 'IP_BLACKLISTED',
                'retry_after' => $this->getBlacklistRemainingTime($clientIp)
            ], 429); // Too Many Requests
        }

        // Bearer token kontrolü
        $token = $request->bearerToken();

        if (!$token || $token !== self::ALLOWED_TOKEN) {
            // Başarısız deneme sayısını artır
            $this->incrementFailedAttempts($clientIp);

            // Maksimum deneme sayısına ulaşıldıysa IP'yi blacklist'e ekle
            if ($this->getFailedAttempts($clientIp) >= self::MAX_ATTEMPTS) {
                $this->blacklistIp($clientIp);

                return response()->json([
                    'success' => false,
                    'message' => 'Çok fazla başarısız deneme. IP adresiniz 10 dakika boyunca engellenmiştir.',
                    'error' => 'IP_BLACKLISTED',
                    'retry_after' => self::BLACKLIST_DURATION * 60
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veya eksik bearer token.',
                'error' => 'INVALID_TOKEN',
                'remaining_attempts' => self::MAX_ATTEMPTS - $this->getFailedAttempts($clientIp)
            ], 401); // Unauthorized
        }

        // Token geçerli, başarısız denemeleri sıfırla
        $this->resetFailedAttempts($clientIp);

        return $next($request);
    }

    /**
     * IP'nin blacklist'te olup olmadığını kontrol eder
     */
    private function isIpBlacklisted(string $ip): bool
    {
        return Cache::has("blacklist_{$ip}");
    }

    /**
     * IP'yi blacklist'e ekler
     */
    private function blacklistIp(string $ip): void
    {
        Cache::put("blacklist_{$ip}", true, self::BLACKLIST_DURATION * 60);

        // Başarısız denemeleri temizle
        $this->resetFailedAttempts($ip);
    }

    /**
     * Blacklist kalan süresini döndürür (saniye)
     */
    private function getBlacklistRemainingTime(string $ip): int
    {
        $key = "blacklist_{$ip}";
        if (!Cache::has($key)) {
            return 0;
        }

        // Cache'deki kalan süreyi hesapla
        $store = Cache::getStore();
        if (method_exists($store, 'getPrefix')) {
            $prefixedKey = $store->getPrefix() . $key;
        } else {
            $prefixedKey = $key;
        }

        // Basit yaklaşım: varsayılan olarak tam süreyi döndür
        return self::BLACKLIST_DURATION * 60;
    }

    /**
     * Başarısız deneme sayısını artırır
     */
    private function incrementFailedAttempts(string $ip): void
    {
        $key = "failed_attempts_{$ip}";
        $attempts = Cache::get($key, 0) + 1;

        // 1 saat boyunca sakla
        Cache::put($key, $attempts, 3600);
    }

    /**
     * Başarısız deneme sayısını döndürür
     */
    private function getFailedAttempts(string $ip): int
    {
        return Cache::get("failed_attempts_{$ip}", 0);
    }

    /**
     * Başarısız denemeleri sıfırlar
     */
    private function resetFailedAttempts(string $ip): void
    {
        Cache::forget("failed_attempts_{$ip}");
    }
}
