<?php

namespace App\Traits;

use App\Helpers\IdCrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Replaces plain integer IDs in URLs with encrypted tokens and logs tampering.
 */
trait HasEncryptedRouteKey
{
    public function getRouteKey(): string
    {
        return IdCrypt::encode($this->getKey());
    }

    public function resolveRouteBinding(mixed $value, $field = null): ?static
    {
        $token = (string) $value;
        $id = IdCrypt::decode($token);

        if ($id === null) {
            $this->logInvalidRouteKey($token, null, 'invalid_encrypted_id');

            return null;
        }

        $model = static::find($id);

        if (! $model) {
            $this->logInvalidRouteKey($token, $id, 'missing_model_for_encrypted_id');

            return null;
        }

        return $model;
    }

    public function resolveChildRouteBinding($childType, $value, $field): ?Model
    {
        return parent::resolveChildRouteBinding($childType, $value, $field);
    }

    protected function logInvalidRouteKey(string $token, ?int $decodedId, string $reason): void
    {
        $request = request();
        $ip = $request?->ip() ?? 'unknown';
        $cacheKey = sprintf('security:invalid-route:%s:%s:%s', static::class, $ip, $reason);
        $attempts = Cache::increment($cacheKey);
        Cache::put($cacheKey, $attempts, now()->addMinutes(30));

        Log::warning('Suspicious encrypted route access detected.', [
            'model' => static::class,
            'reason' => $reason,
            'token' => $token,
            'decoded_id' => $decodedId,
            'attempts' => $attempts,
            'ip' => $ip,
            'url' => $request?->fullUrl(),
            'user_id' => auth()->id(),
        ]);

        if ($attempts >= 5) {
            Log::alert('Multiple invalid encrypted route access attempts detected.', [
                'model' => static::class,
                'reason' => $reason,
                'attempts' => $attempts,
                'ip' => $ip,
                'user_id' => auth()->id(),
            ]);
        }
    }
}
