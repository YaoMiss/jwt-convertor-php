<?php
/**
 * Created by PhpStorm.
 * User: Packie
 * Date: 2021/8/9 14:20
 */

namespace Shota\JWT;

use Shota\JWT\Exception\Runtime\InvalidException;
use Shota\JWT\Exception\Runtime\UnsupportException;

class JWT
{
    public const ENCRYPT_LIBRARY_PHP_HASH_HMAC = 'hash_hmac';
    public const ENCRYPT_LIBRARY_PHP_SODIUM_CRYPTO = 'sodium_crypto';
    public const ENCRYPT_LIBRARY_OPENSSL = 'openssl';

    public static $supportAlgorithm = [
        // HMAC with SHA-2
        'HS256' => ['lib' => self::ENCRYPT_LIBRARY_PHP_HASH_HMAC, 'method' => 'SHA256'],
        'HS384' => ['lib' => self::ENCRYPT_LIBRARY_PHP_HASH_HMAC, 'method' => 'SHA384'],
        'HS512' => ['lib' => self::ENCRYPT_LIBRARY_PHP_HASH_HMAC, 'method' => 'SHA512'],

        'EdDSA' => ['lib' => self::ENCRYPT_LIBRARY_PHP_SODIUM_CRYPTO, 'method' => 'EdDSA'],

        // EC DSA signature with SHA-2
        'ES256' => ['lib' => self::ENCRYPT_LIBRARY_OPENSSL, 'method' => OPENSSL_ALGO_SHA256], // Recommended . short
        'ES384' => ['lib' => self::ENCRYPT_LIBRARY_OPENSSL, 'method' => OPENSSL_ALGO_SHA384],
        'ES512' => ['lib' => self::ENCRYPT_LIBRARY_OPENSSL, 'method' => OPENSSL_ALGO_SHA512],

        // RSA signature with PKCS #1 and SHA-2
        'RS256' => ['lib' => self::ENCRYPT_LIBRARY_OPENSSL, 'method' => OPENSSL_ALGO_SHA256], // Recommended+ . long
        'RS384' => ['lib' => self::ENCRYPT_LIBRARY_OPENSSL, 'method' => OPENSSL_ALGO_SHA384],
        'RS512' => ['lib' => self::ENCRYPT_LIBRARY_OPENSSL, 'method' => OPENSSL_ALGO_SHA512],

        // RSA PSS signature with SHA-2
        /*
         'PS256' => [],
         'PS384' => [],
         'PS512' => [],
        */
    ];

    /**
     * @throws \JsonException|\SodiumException
     */
    public static function encode(array $payload, string $key, string $algo = 'HS256'): string
    {
        $header = ['typ' => 'JWT', 'alg' => $algo];  // default header

        $segments[] = self::urlSafeB64Encode(json_encode($header, JSON_THROW_ON_ERROR));
        $segments[] = self::urlsafeB64Encode(json_encode($payload, JSON_THROW_ON_ERROR));
        $segments[] = self::urlsafeB64Encode(self::sign(implode('.', $segments), $key, $algo));

        return implode('.', $segments);
    }

    public static function decode(string $jwtStr, string $publicKey)
    {
        $encryptSegments = explode('.', $jwtStr);

        if (count($encryptSegments) !== 3) {
            throw new InvalidException('Wrong number of segments');
        }
        [$encodeHeader, $encodePayload, $encodeSignature] = $encryptSegments;
        try {
            $header = json_decode(self::urlSafeB64Decode($encodeHeader), true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode(self::urlSafeB64Decode($encodePayload), true, 512, JSON_THROW_ON_ERROR);
            $signature = self::urlSafeB64Decode($encodeSignature);
        } catch (\Throwable $exception) {
            throw new InvalidException(sprintf('Cannot decode the jwt ,maybe it is wrong ,msg : %s', $exception->getMessage()));
        }
        if (!isset(self::$supportAlgorithm[$header['alg']])) {
            throw new UnsupportException('Jwt encrypt algorithm not support');
        }
        try {
            if (!self::verify(sprintf('%s.%s', $encodeHeader, $encodePayload), $signature, $publicKey, $header['alg'])) {
                throw new InvalidException('Verify data error');
            }
        } catch (\Throwable $exception) {
            throw new InvalidException(sprintf('Verify data error , msg : %s', $exception->getMessage()));
        }

        return $payload;
    }

    /**
     * @throws \SodiumException
     */
    private static function verify(string $segmentsStr, string $validateSignature, string $publicKey, string $algo): bool
    {
        [$lib, $method] = array_values(self::$supportAlgorithm[$algo]);
        switch ($lib) {
            case self::ENCRYPT_LIBRARY_OPENSSL:
                return openssl_verify($segmentsStr, $validateSignature, $publicKey, $method) === 1;
            case self::ENCRYPT_LIBRARY_PHP_SODIUM_CRYPTO:
                return sodium_crypto_sign_verify_detached($validateSignature, $segmentsStr, $publicKey);
            case self::ENCRYPT_LIBRARY_PHP_HASH_HMAC:
            default:
                $reHash = hash_hmac($method, $segmentsStr, $publicKey, true);

                return strcasecmp($validateSignature, $reHash) === 0;
        }
    }

    /**
     * @throws \SodiumException
     */
    public static function sign(string $segmentsStr, string $privateKey, $algo = 'HS256'): string
    {
        if (empty(self::$supportAlgorithm[$algo])) {
            throw new UnsupportException('Algorithm not supported');
        }

        [$lib, $method] = array_values(self::$supportAlgorithm[$algo]);

        switch ($lib) {
            case self::ENCRYPT_LIBRARY_OPENSSL:
                $signature = null;
                if (!openssl_sign($segmentsStr, $signature, $privateKey, $method)) {
                    throw new InvalidException('Openssl unable to sign data');
                }

                return $signature;
            case self::ENCRYPT_LIBRARY_PHP_SODIUM_CRYPTO:
                return sodium_crypto_sign_detached($segmentsStr, $privateKey);
            case self:: ENCRYPT_LIBRARY_PHP_HASH_HMAC:
            default:
                return hash_hmac($method, $segmentsStr, $privateKey, true);
        }
    }

    /**
     * @notice Some char in url will be converted
     *
     * '=' => '', '+' => '-' ,'/'=>'_'
     *
     * @param string $input
     * @return string
     */
    public static function urlSafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    public static function urlSafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $input .= str_repeat('=', $padLen);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }
}