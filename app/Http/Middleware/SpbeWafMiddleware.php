<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SpbeGatewayController;
use App\Models\SecurityAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SpbeWafMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('gateway/login')) {
            return $next($request);
        }

        $startedAt = hrtime(true);
        $rawTarget = urldecode($request->getRequestUri()) . ' ' . json_encode(
            $request->except(['password', 'photo_base64', '_token'])
        );

        $threat = (new SpbeGatewayController())->inspectPayload($rawTarget);

        if ($threat !== null) {
            $latencyMs = round((hrtime(true) - $startedAt) / 1_000_000, 3);
            $clientIp = $request->header('X-Simulated-IP') ?: ($request->ip() ?? '127.0.0.1');

            SecurityAuditLog::create([
                'source' => 'WEB_WAF',
                'nip' => 'HTTP-GUARD',
                'ip_address' => $clientIp,
                'protocol' => 'HTTP/' . $request->method(),
                'event_type' => $threat['type'],
                'severity' => $threat['severity'],
                'status' => 'BLOCKED',
                'detection_latency_ms' => $latencyMs,
                'payload_snippet' => mb_substr(urldecode($request->getRequestUri()), 0, 120),
                'recommended_action' => $threat['action'],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'BLOCKED',
                    'event_type' => $threat['type'],
                    'latency_ms' => $latencyMs,
                    'message' => "Serangan {$threat['type']} diblokir oleh SPBE Web WAF.",
                ], 403);
            }

            abort(403, "Serangan {$threat['type']} terdeteksi dan diblokir oleh SPBE Web WAF.");
        }

        return $next($request);
    }
}