<?php
/**
 * Created by PhpStorm.
 * User: Packie
 * Date: 2021/8/9 14:29
 */

namespace Shota\JWT\Test;

use PHPUnit\Framework\TestCase;
use Shota\JWT\Exception\Runtime\InvalidException;
use Shota\JWT\Exception\Runtime\UnsupportException;
use Shota\JWT\JWT;

class ExceptionTest extends TestCase
{
    public function testJWtStrError(): void
    {
        $mockJwtStr = sprintf('%s.%s', 'dsaadsdsasd', 'sdsfsdfasf');

        $this->expectException(InvalidException::class);


        JWT::decode($mockJwtStr, '11111');

    }

    public function testErrorPayloadJson(): void
    {
        $mockJwtStr = sprintf('%s.%s.%s', 'dsaadsdsasd', 'sdsfsdfasf', 'asdasdadasd');
        $this->expectException(UnsupportException::class);

        JWT::decode($mockJwtStr, '11111');
    }

    /**
     * @throws \SodiumException
     */
    public function testSignAlgoNotSupport(): void
    {
        $this->expectException(UnsupportException::class);
        Jwt::sign('sdfsdf', 'dsfdsfsf', 'PS3841');
    }

    /**
     * @throws \SodiumException
     */
    public function testSignError(): void
    {
        $this->expectWarning();
        Jwt::sign('sdfsdf', 'dsfdsfsf', 'ES256');
    }

    /**
     * @throws \SodiumException
     */
    public function testDecodeNotSupportAlgo(): void
    {
        $header = ['typ' => 'JWT', 'alg' => 'PS256-not-support'];

        $payload = [
            "iss" => "packie-test-12",
            "exp" => time() + 100000,
            "sub" => "unit-test-01",
            "aud" => "all",
            "nbf" => time(),
            "iat" => time(),
            "jti" => time(),

            "name" => "John Doe",
            "admin" => true,
        ];

        $segments[] = JWT::urlSafeB64Encode(json_encode($header));
        $segments[] = JWT::urlsafeB64Encode(json_encode($payload));
        $segments[] = JWT::urlsafeB64Encode(JWT::sign(implode('.', $segments), '11111'));

        $jwtStr = implode('.', $segments);

        $this->expectException(UnsupportException::class);

        JWT::decode($jwtStr, '11111');
    }
}
