<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class QrTokenService
{
    /**
     * Generate an encrypted token containing timestamp, unique salt, and unit_id.
     */
    public function generateToken(?int $unitId = null): string
    {
        $payload = json_encode([
            'timestamp' => time(),
            'salt' => Str::random(16),
            'unit_id' => $unitId,
        ]);

        return Crypt::encryptString($payload);
    }

    /**
     * Decrypt and validate token against expiration interval and unit_id.
     */
    public function validateToken(string $token, int $maxAgeSeconds = 30, ?int $expectedUnitId = null): bool
    {
        try {
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);

            if (!$payload || !isset($payload['timestamp'])) {
                return false;
            }

            // Expiry check
            $age = time() - (int) $payload['timestamp'];
            if ($age < -5 || $age > $maxAgeSeconds) {
                return false;
            }

            // Unit ID match check (Anti-IDOR)
            if ($expectedUnitId !== null) {
                if (!isset($payload['unit_id']) || (int) $payload['unit_id'] !== (int) $expectedUnitId) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
