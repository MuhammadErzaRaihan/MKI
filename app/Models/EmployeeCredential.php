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

    public function isAllowedInstansiIp(string $ip): bool
    {
        $instansiMap = config('services.spbe.instansi_cidrs', []);

        foreach ($instansiMap as $keyword => $cidrs) {
            if (stripos((string) $this->instansi, (string) $keyword) !== false) {
                return self::ipMatchesCidrs($ip, (array) $cidrs);
            }
        }

        return self::isOfficeIp($ip);
    }

    public static function ipMatchesCidrs(string $ip, array $cidrs): bool
    {
        $ipBin = @inet_pton($ip);
        if ($ipBin === false) {
            return false;
        }

        foreach ($cidrs as $cidr) {
            [$subnet, $bits] = array_pad(explode('/', trim((string) $cidr), 2), 2, null);
            $subnetBin = @inet_pton($subnet);
            if ($subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
                continue;
            }

            $maxBits = strlen($ipBin) * 8;
            $prefix = $bits === null ? $maxBits : max(0, min($maxBits, (int) $bits));
            $fullBytes = intdiv($prefix, 8);
            $remBits = $prefix % 8;

            if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
                continue;
            }

            if ($remBits > 0) {
                $mask = (0xFF << (8 - $remBits)) & 0xFF;
                if ((ord($ipBin[$fullBytes]) & $mask) !== (ord($subnetBin[$fullBytes]) & $mask)) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }
}