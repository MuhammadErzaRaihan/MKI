<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\IpUtils;

class EmployeeCredential extends Model
{
    protected $fillable = [
        'nip',
        'nama',
        'email',
        'password',
        'instansi',
        'ttl',
        'telp',
        'status',
        'ip_detected',
        'is_office_ip',
        'secret_key',
        'otp_code',
        'otp_expires_at',
        'photo_path',
    ];

    protected $casts = [
        'is_office_ip' => 'boolean',
        'otp_expires_at' => 'datetime',
    ];

    protected $hidden = ['password'];

    protected $appends = [
        'photo_url',
        'ip_label',
    ];

    public function auditLogs(): HasMany
    {
        return $this->hasMany(SecurityAuditLog::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::url($this->photo_path);
        }

        $initials = urlencode(strtok($this->nama, ' ') ?: 'ID');
        return "https://placehold.co/120x150/0066D6/ffffff?text={$initials}";
    }

    public function getIpLabelAttribute(): string
    {
        $ip = $this->ip_detected ?? '127.0.0.1';
        $scope = $this->is_office_ip ? 'IP Kantor' : 'Luar Kantor';

        return "{$ip} ({$scope})";
    }

    public static function isOfficeIp(string $ip): bool
    {
        $officeCidrs = config('services.spbe.office_cidrs', [
            '127.0.0.1/32',
            '::1/128',
            '192.168.0.0/16',
            '10.0.0.0/8',
        ]);

        return IpUtils::checkIp($ip, $officeCidrs);
    }

    // Alias kept for CredentialController callers.
    public static function isOfficeNetwork(string $ip): bool
    {
        return self::isOfficeIp($ip);
    }
}