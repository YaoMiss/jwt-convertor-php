# PHP JWT Convertor

## Dependence

* PHP version : >=7.0.0|^8.0

* php-ext : openssl 、mbstring 、sodium

## Installation

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