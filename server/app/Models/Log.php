<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * İstek Log Modeli
 *
 * Tüm API isteklerini IP adresi ile birlikte kaydeder
 */
class Log extends Model
{
    /**
     * UUID primary key kullanımı için Laravel'e bildiriyoruz
     */
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ip_address',
        'method',
        'url',
        'user_agent',
        'headers',
        'request_data',
        'response_status',
        'response_time',
        'bearer_token_used',
        'is_authenticated',
    ];

    protected $casts = [
        'headers' => 'array',
        'request_data' => 'array',
        'is_authenticated' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Log oluşturulurken UUID otomatik oluşturuluyor
        static::creating(function ($log) {
            if (empty($log->id)) {
                $log->id = Str::uuid()->toString();
            }
        });
    }

    /**
     * IP adresine göre logları filtrele
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * HTTP metoduna göre logları filtrele
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', strtoupper($method));
    }

    /**
     * Kimlik doğrulama durumuna göre filtrele
     */
    public function scopeAuthenticated($query, bool $authenticated = true)
    {
        return $query->where('is_authenticated', $authenticated);
    }

    /**
     * Belirli bir tarih aralığındaki logları getir
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
