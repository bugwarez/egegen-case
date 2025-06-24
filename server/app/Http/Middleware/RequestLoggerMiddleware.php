<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * İstek Logger Middleware
 *
 * Tüm gelen istekleri otomatik olarak logs tablosuna kaydeder
 * IP adresi, istek detayları ve yanıt bilgilerini içerir
 */
class RequestLoggerMiddleware
{
    /**
     * Gelen isteği işle ve logla
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // İsteği işle
        $response = $next($request);

        // Yanıt süresini hesapla
        $responseTime = round((microtime(true) - $startTime) * 1000); // ms cinsinden

        // İsteği asenkron olarak logla (performans için)
        $this->logRequest($request, $response, $responseTime);

        return $response;
    }

    /**
     * İsteği veritabanına kaydet
     *
     * @param Request $request
     * @param Response $response
     * @param int $responseTime
     */
    private function logRequest(Request $request, Response $response, int $responseTime): void
    {
        try {
            // Hassas bilgileri filtrele
            $headers = $this->filterSensitiveHeaders($request->headers->all());
            $requestData = $this->filterSensitiveData($request->all());

            // Bearer token varsa hash'le (güvenlik için)
            $bearerTokenHash = null;
            if ($request->bearerToken()) {
                $bearerTokenHash = hash('sha256', $request->bearerToken());
            }

            // Kimlik doğrulama durumunu kontrol et
            $isAuthenticated = $this->checkAuthentication($request);

            Log::create([
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'headers' => $headers,
                'request_data' => $requestData,
                'response_status' => $response->getStatusCode(),
                'response_time' => $responseTime,
                'bearer_token_used' => $bearerTokenHash,
                'is_authenticated' => $isAuthenticated,
            ]);

        } catch (\Exception $e) {
            // Log kaydında hata olursa uygulamayı etkilemesin
            // Sadece Laravel log'una yaz
            \Log::error('Request logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Hassas başlıkları filtrele
     *
     * @param array $headers
     * @return array
     */
    private function filterSensitiveHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
        ];

        $filtered = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $filtered[$key] = ['***FILTERED***'];
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Hassas request verilerini filtrele
     *
     * @param array $data
     * @return array
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
        ];

        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $filtered[$key] = '***FILTERED***';
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * İsteğin kimlik doğrulama durumunu kontrol et
     *
     * @param Request $request
     * @return bool
     */
    private function checkAuthentication(Request $request): bool
    {
        // Bearer token kontrolü
        $token = $request->bearerToken();
        if ($token && $token === '2BH52wAHrAymR7wP3CASt') {
            return true;
        }

        // Sanctum token kontrolü (eğer varsa)
        if ($request->user()) {
            return true;
        }

        return false;
    }
}
