<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    protected $fillable = [
        'source',
        'nip',
        'ip_address',
        'destination_ip',
        'protocol',
        'event_type',
        'severity',
        'status',
        'detection_latency_ms',
        'payload_snippet',
        'recommended_action',
    ];
}