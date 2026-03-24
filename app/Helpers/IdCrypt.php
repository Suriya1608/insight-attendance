<?php

namespace App\Helpers;

/**
 * Short, URL-safe AES-128-ECB encryption for integer IDs.
 * Produces 22-char base64url strings (no external packages needed).
 * Uses OPENSSL_NO_PADDING so the 16-byte padded input maps to exactly
 * 16 bytes of ciphertext — no PKCS7 padding block appended.
 */
class IdCrypt
{
    private const CIPHER = 'AES-128-ECB';
    private const FLAGS  = OPENSSL_RAW_DATA | OPENSSL_NO_PADDING;

    private static function key(): string
    {
        // Derive a 16-byte (128-bit) key from the application key.
        return substr(md5(config('app.key')), 0, 16);
    }

    /**
     * Encode an integer ID to a 22-char URL-safe encrypted string.
     */
    public static function encode(int $id): string
    {
        $data      = str_pad((string) $id, 16, "\0", STR_PAD_LEFT);
        $encrypted = openssl_encrypt($data, self::CIPHER, self::key(), self::FLAGS);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    /**
     * Decode a URL-safe encrypted string back to an integer ID.
     * Returns null if the value is invalid or tampered.
     */
    public static function decode(string $hash): ?int
    {
        try {
            $padded    = $hash . str_repeat('=', (4 - strlen($hash) % 4) % 4);
            $decoded   = base64_decode(strtr($padded, '-_', '+/'), true);

            if ($decoded === false || strlen($decoded) !== 16) {
                return null;
            }

            $decrypted = openssl_decrypt($decoded, self::CIPHER, self::key(), self::FLAGS);

            if ($decrypted === false) {
                return null;
            }

            $id = (int) ltrim($decrypted, "\0");

            return $id > 0 ? $id : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
