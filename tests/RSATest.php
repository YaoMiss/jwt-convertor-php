<?php
/**
 * Created by PhpStorm.
 * User: Packie
 * Date: 2021/8/6 14:40
 */

namespace Shota\JWT\Test;

use PHPUnit\Framework\TestCase;
use Shota\JWT\JWT;

class RSATest extends TestCase
{
    private $privateKey;

    private $publicKey;


    protected function setUp(): void
    {
        parent::setUp();
        $this->privateKey = file_get_contents(__DIR__ . '/cert/rsa-private-key.pem');
        $this->publicKey = file_get_contents(__DIR__ . "/cert/rsa-public-key.pem");
    }

    /**
     * @throws \SodiumException
     * @throws \JsonException
     */
    public function testEncode(): array
    {
        $payload = [
            "iss" => "packie",
            "exp" => time() + 100000,
            "sub" => "unit-test-01",
            "aud" => "all",
            "nbf" => time(),
            "iat" => time(),
            "jti" => time(),

            "name" => "John Doe",
            "admin" => true,
        ];
        /** @var mixed $jwtStr */
        $jwtStr = JWT::encode($payload, $this->privateKey, 'RS256');
        self::assertIsString($jwtStr);

        return compact('jwtStr', 'payload');
    }

    /**
     * @depends testEncode
     */
    public function testDecode(array $data): void
    {
        $payload = JWT::decode($data['jwtStr'], $this->publicKey);

        self::assertEquals($data['payload']['iss'], $payload['iss']);
    }
}
