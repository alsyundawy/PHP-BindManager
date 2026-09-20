<?php

declare(strict_types=1);

namespace App\Services\Auth;

use InvalidArgumentException;

/**
 * Pure RFC 6238 Time-Based One-Time Password (TOTP) implementation.
 */
final class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 16): string
    {
        $validChars = self::BASE32_ALPHABET;
        $secret     = '';
        $max        = strlen($validChars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[random_int(0, $max)];
        }

        return $secret;
    }

    public function getOtp(string $secret, ?int $timeSlice = null): string
    {
        $timeSlice ??= (int) floor(time() / 30);
        $secretKey = $this->base32Decode($secret);

        $timeBinary = pack('N*', 0) . pack('N*', $timeSlice);
        $hash       = hash_hmac('sha1', $timeBinary, $secretKey, true); // DevSkim: ignore DS126858,DS197836
        $offset     = ord($hash[19]) & 0x0f;

        $part1 = (ord($hash[$offset]) & 0x7f)     << 24;
        $part2 = (ord($hash[$offset + 1]) & 0xff) << 16;
        $part3 = (ord($hash[$offset + 2]) & 0xff) << 8;
        $part4 = ord($hash[$offset + 3]) & 0xff;

        $otp = ($part1 | $part2 | $part3 | $part4) % 1_000_000;

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code, int $discrepancy = 1): bool
    {
        $currentTimeSlice = (int) floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedOtp = $this->getOtp($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedOtp, trim($code))) {
                return true;
            }
        }

        return false;
    }

    public function getProvisioningUri(
        string $secret,
        string $accountName,
        string $issuer = 'PHP-BindManager'
    ): string {
        $encodedIssuer  = rawurlencode($issuer);
        $encodedAccount = rawurlencode($accountName);

        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30', // DevSkim: ignore DS126858
            $encodedIssuer,
            $encodedAccount,
            $secret,
            $encodedIssuer
        );
    }

    private function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        if ($b32 === '') {
            return '';
        }

        $alphabet = self::BASE32_ALPHABET;
        $buffer   = 0;
        $bitsLeft = 0;
        $output   = '';

        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $val = strpos($alphabet, $b32[$i]);
            if ($val === false) {
                throw new InvalidArgumentException('Invalid character in Base32 string.');
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $output;
    }
}
