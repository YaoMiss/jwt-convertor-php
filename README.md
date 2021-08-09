# PHP JWT Convertor

## Dependence

* PHP version : `>=7.2.0|^8.0`

* php-ext : `openssl` 、`mbstring` 、`sodium`

## Algorithm

| Algo                                   | Incluing          | Support | Comment     |
|----------------------------------------|-------------------|---------|-------------|
| HMAC with SHA-2                        | HS256 HS384 HS512 | Yes     |             |
| EC DSA signature with SHA-2            | ES256 ES384 ES512 | Yes     | Short Slow  |
| RSA signature with PKCS #1 and SHA-2   | RS256 RS384 RS512 | Yes     | Long Fast |
| RSA PSS signature with SHA-2           | PS256 PS384 PS512 | No      |             |
| Edwards-curve DSA signature with SHA-2 | EdDSA             | Yes     |             |


## Installation

```bash
composer require shota/jwt-covertor
```

## Generate Key

## RSA (RSA signature with PKCS #1 and SHA-2)

> Advance : Key long but fast

```bash
# generate the private key
bash > openssl
> genrsa -out rsa_private_key.pem 2048

# generate the public key
bash > openssl
> rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem
```

## ECS (EC DSA signature with SHA-2)

> Advance : Key short but slow

```bash
# generate the private key
bash > openssl ecparam -name secp256k1 -genkey -out privateKey.pem

# generate the public key
bash > openssl ec -in privateKey.pem -pubout -out publicKey.pem
```

## EdDSA (Edwards-curve DSA signature with SHA-2)

```php
// $sign_seed = random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES);
// $sign_pair = sodium_crypto_sign_seed_keypair($sign_seed);

$sign_pair = sodium_crypto_sign_keypair();
$sign_secret = sodium_crypto_sign_secretkey($sign_pair);
$sign_public = sodium_crypto_sign_publickey($sign_pair);

$message = 'Hello';

$signature = sodium_crypto_sign_detached($message, $sign_secret);
$message_valid = sodium_crypto_sign_verify_detached($signature, $message, $sign_public);
```


## Usage 

### Example With HMAC

```php
use Shota\JWT\JWT;

$key = 'encode-key';
$payload = [
        "iss" => "John Doe",
        "exp" => time() + 100000,
        "sub" => "unit-test-01",
        "aud" => "all",
        "nbf" => time(),
        "iat" => time(),
        "jti" => time(),

        "name" => "John Doe",
        "admin" => true,
];
$jwtStr = JWT::encode($this->payload, $key, 'HS512');
echo sprintf('jwt : %s \n',jwtStr);

$decodePayload = JWT::decode($jwtStr,$key)
print_r($decodePayload);
```

### Example With EC DSA Signature 

```php
use Shota\JWT\JWT;

$privateKey = file_get_contents(__DIR__ . '/cert/ecs-private-key.pem');
$publicKey =  file_get_contents(__DIR__ . '/cert/ecs-public-key.pem');

$payload = [
    "iss" => "John Doe",
    "exp" => time() + 100000,
    "sub" => "unit-test-01",
    "aud" => "all",
    "nbf" => time(),
    "iat" => time(),
    "jti" => time(),

    "name" => "John Doe",
    "admin" => true,
];
$jwtStr = JWT::encode($this->payload, $privateKey, 'HS512');
echo sprintf('jwt : %s \n',jwtStr);

$decodePayload = JWT::decode($jwtStr,$publicKey)
print_r($decodePayload);
```

### Example With RSA Signature 

```php
use Shota\JWT\JWT;

$privateKey = file_get_contents(__DIR__ . '/cert/rsa-private-key.pem');
$publicKey = file_get_contents(__DIR__ . "/cert/rsa-public-key.pem");

$payload = [
    "iss" => "John Doe",
    "exp" => time() + 100000,
    "sub" => "unit-test-01",
    "aud" => "all",
    "nbf" => time(),
    "iat" => time(),
    "jti" => time(),

    "name" => "John Doe",
    "admin" => true,
];

$jwtStr = JWT::encode($this->payload, $privateKey, 'HS512');
echo sprintf('jwt : %s \n',jwtStr);

$decodePayload = JWT::decode($jwtStr,$publicKey)
print_r($decodePayload);
```

### Example With EdDSA  

```php
use Shota\JWT\JWT;

$payload = [
    "iss" => "John Doe",
    "exp" => time() + 100000,
    "sub" => "unit-test-01",
    "aud" => "all",
    "nbf" => time(),
    "iat" => time(),
    "jti" => time(),

    "name" => "John Doe",
    "admin" => true,
];

$signPair = sodium_crypto_sign_keypair();
$secret = sodium_crypto_sign_secretkey($signPair);
$publicKey = sodium_crypto_sign_publickey($signPair);

$jwtStr = JWT::encode($payload, $secret, 'EdDSA');
echo sprintf('jwt : %s \n',jwtStr);

$decodePayload = JWT::decode(jwtStr, publicKey);
print_r($decodePayload);
```

## Tests

```
bash > composer require --dev phpunit/phpunit
bash > vendor/bin/phpunit  --configuration phpunit.xml.dist
```

