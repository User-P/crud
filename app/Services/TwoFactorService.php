<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TwoFactorService
{
    /**
     * Generate a new Base32 secret compatible with authenticator apps.
     */
    public function generateSecret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $secret;
    }

    /**
     * Generate a list of recovery codes the user can use when they lose access
     * to their authenticator app.
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $amount = 8): array
    {
        return Collection::times($amount, function () {
            return Str::upper(Str::random(10));
        })->toArray();
    }

    /**
     * Build the otpauth URL that authenticator apps understand (used for QR codes).
     */
    public function qrCodeUrl(string $email, string $secret): string
    {
        $issuer = rawurlencode(config('app.name', 'Laravel'));
        $email = rawurlencode($email);

        return sprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s', $issuer, $email, $secret, $issuer);
    }

    /**
     * Validate the code entered by the user with a rolling window tolerance.
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code ?? '');

        if (!preg_match('/^\d{6}$/', (string) $code)) {
            return false;
        }

        $timeSlice = (int) floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            if ($this->generateOneTimePassword($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the 6 digit code for a given time slice.
     */
    protected function generateOneTimePassword(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);

        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = unpack('N', substr($hash, $offset, 4))[1];
        $value = ($value & 0x7FFFFFFF) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Convert a Base32 string into binary.
     */
    protected function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $secret = preg_replace('/[^' . $alphabet . ']/', '', $secret);

        $binaryString = '';
        $buffer = 0;
        $bitsLeft = 0;

        foreach (str_split($secret) as $char) {
            $buffer = ($buffer << 5) | strpos($alphabet, $char);
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binaryString .= chr(($buffer & (0xFF << $bitsLeft)) >> $bitsLeft);
            }
        }

        return $binaryString;
    }
}
