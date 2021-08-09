<?php
/**
 * Created by PhpStorm.
 * User: Packie
 * Date: 2021/8/8 21:36
 */

namespace Shota\JWT\Test;

use PHPUnit\Framework\TestCase;
use Shota\JWT\JWT;

class EdDSATest extends TestCase
{
    private $payload;

    /**
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = [
            "iss" => "packie-test",
            "exp" => time() + 100000,
            "sub" => "unit-test-01",
            "aud" => "all",
            "nbf" => time(),
            "iat" => time(),
            "jti" => time(),

            "name" => "John Doe",
            "admin" => true,
        ];

    }


    /**
     * @throws \SodiumException
     * @throws \JsonException
     */
    public function testEncode(): array
    {
        $signPair = sodium_crypto_sign_keypair();
        $secret = sodium_crypto_sign_secretkey($signPair);
        $publicKey = sodium_crypto_sign_publickey($signPair);

        /** @var mixed $jwtStr */
        $jwtStr = JWT::encode($this->payload, $secret, 'EdDSA');
        self::assertIsString($jwtStr);

        return compact('jwtStr', 'publicKey');
    }

    /**
     * @param array $data
     * @depends testEncode
     */
    public function testDecode(array $data): void
    {
        $payload = JWT::decode($data['jwtStr'], $data['publicKey']);

        self::assertEquals($this->payload['exp'], $payload['exp']);
    }
}
