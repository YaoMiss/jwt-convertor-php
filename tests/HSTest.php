<?php
/**
 * Created by PhpStorm.
 * User: Packie
 * Date: 2021/8/6 17:28
 */

namespace Shota\JWT\Test;

use PHPUnit\Framework\TestCase;
use Shota\JWT\JWT;

class HSTest extends TestCase
{
    private $key = 'test01';

    private $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = [
            "iss" => "packie01",
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
        /** @var mixed $jwtStr */
        $jwtStr = JWT::encode($this->payload, $this->key, 'HS512');
        self::assertIsString($jwtStr);

        return compact('jwtStr');
    }

    /**
     * @param array $data
     * @return void
     * @depends testEncode
     */
    public function testDecode(array $data): void
    {
        $payload = JWT::decode($data['jwtStr'], $this->key);

        self::assertEquals($this->payload['exp'], $payload['exp']);
    }
}
