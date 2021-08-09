<?php
/**
 * Created by PhpStorm.
 * User: Packie
 * Date: 2021/8/8 22:05
 */

namespace Shota\JWT\Test;

use PHPUnit\Framework\TestCase;

class ECSTest extends TestCase
{
    private $privateKey;

    private $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->privateKey = file_get_contents(__DIR__ . '/cert/ecs-private-key.pem');
        $this->publicKey = file_get_contents(__DIR__ . '/cert/ecs-public-key.pem');
    }

    /**
     * @throws \SodiumException
     */
    public function testEncode(): array
    {
        $payload = [
            "iss" => "packie-ecs-test",
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
        $jwtStr = \Shota\JWT\JWT::encode($payload, $this->privateKey, 'ES256');
        self::assertIsString($jwtStr);

        return compact('jwtStr', 'payload');
    }

    /**
     * @depends testEncode
     */
    public function testDecode(array $data): void
    {
        $payload = \Shota\JWT\JWT::decode($data['jwtStr'], $this->publicKey);

        self::assertEquals($data['payload']['iss'], $payload['iss']);
    }
}
