<?php

namespace App\Http\Middleware;

use App\Models\UserDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserDevice
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        $deviceFingerprint = $this->generateFingerprint($request);

        // Find or create device record
        $device = UserDevice::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'fingerprint' => $deviceFingerprint,
            ],
            [
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'is_trusted' => false,
                'last_activity_at' => now(),
            ]
        );

        // Update last activity
        $device->update([
            'last_activity_at' => now(),
            'ip_address' => $ipAddress,
        ]);

        // Flag suspicious devices
        if ($device->ip_address !== $ipAddress && !$device->is_trusted) {
            \Illuminate\Support\Facades\Log::warning('Suspicious device activity', [
                'user_id' => $request->user()->id,
                'old_ip' => $device->ip_address,
                'new_ip' => $ipAddress,
                'device_id' => $device->id,
            ]);
        }

        $request->merge(['user_device' => $device]);

        return $next($request);
    }

    /**
     * Generate device fingerprint from request data.
     */
    private function generateFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', $components));
    }
}
