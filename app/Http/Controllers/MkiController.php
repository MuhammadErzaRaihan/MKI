<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use Illuminate\View\View;

class MkiController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_events' => SecurityAuditLog::count(),
            'blocked_count' => SecurityAuditLog::where('status', 'BLOCKED')->count(),
            'suspicious_count' => SecurityAuditLog::where('status', 'SUSPICIOUS')->count(),
            'allowed_count' => SecurityAuditLog::where('status', 'ALLOWED')->count(),
            'avg_latency_ms' => round((float) SecurityAuditLog::avg('detection_latency_ms'), 2),
            'active_asn' => EmployeeCredential::where('status', 'ON')->count(),
        ];

        $recentLogs = SecurityAuditLog::latest()->limit(5)->get();

        return view('mki', compact('stats', 'recentLogs'));
    }
}