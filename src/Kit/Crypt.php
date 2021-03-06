<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use Ep\Exception\CryptException;
use InvalidArgumentException;

final class Crypt
{
    private string $method;

    public function __construct(private Config $config)
    {
        $this->method = $config->cipherMethod;
    }

    private ?string $key = null;

    /**
     * @throws InvalidArgumentException
     */
    public function withMethod(string $method, string $key = null): self
    {
        $new = clone $this;
        $new->method = $method;
        $new->key = $key ? base64_decode($key) : $key;
        $new->validate();
        return $new;
    }

    private string $algo = 'sha256';

    public function withHashAlgo(string $algo): self
    {
        $new = clone $this;
        $new->algo = $algo;
        return $new;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getKey(): string
    {
        if ($this->key === null) {
            $this->key = base64_decode($this->config->secretKey ?? '');
            $this->validate();
        }
        return $this->key;
    }

    /**
     * @throws CryptException
     * @throws InvalidArgumentException
     */
    public function encrypt(string $value): string
    {
        $length = openssl_cipher_iv_length($this->method);
        $iv = $length ? random_bytes($length) : '';

        $value = openssl_encrypt($value, $this->method, $this->getKey(), 0, $iv);
        if ($value === false) {
            throw new CryptException('Could not encrypt the data.');
        }

        $mac = $this->hash($iv = base64_encode($iv), $value);

        return base64_encode(json_encode(compact('iv', 'value', 'mac'), JSON_UNESCAPED_SLASHES));
    }

    /**
     * @throws CryptException
     * @throws InvalidArgumentException
     */
    public function decrypt(string $payload): string
    {
        $payload = $this->getJsonPayload($payload);

        $decrypted = openssl_decrypt($payload['value'], $this->method, $this->getKey(), 0, base64_decode($payload['iv']));

        if ($decrypted === false) {
            throw new CryptException('Could not decrypt the data.');
        }

        return $decrypted;
    }

    public function generateKey(): string
    {
        $length = explode('-', $this->method)[1] ?? '';
        $length = is_numeric($length) ? $length : 128;
        return random_bytes(intval($length / 8));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if (!$this->config->debug) {
            return;
        }

        $method = strtolower($this->method);
        if (!in_array($method, openssl_get_cipher_methods())) {
            throw new InvalidArgumentException('Invalid cipher method "' . $this->method . '".');
        }

        $pieces = explode('-', $method);
        if (count($pieces) <= 2) {
            return;
        }

        if (!in_array(substr($pieces[2], 0, 3), ['cbc', 'cfb', 'ctr', 'ecb', 'ofb'])) {
            throw new InvalidArgumentException('The supported cipher modes are CBC, CFB, CTR, ECB and OFB.');
        }

        if (strlen($this->getKey()) !== intval($pieces[1]) / 8) {
            throw new InvalidArgumentException('The secret key length is not correct.');
        }
    }

    /**
     * @throws CryptException
     */
    private function getJsonPayload(string $payload): array
    {
        $payload = json_decode(base64_decode($payload), true);

        if (!$this->validatePayload($payload)) {
            throw new CryptException('The payload is invalid.');
        }

        if (!$this->validateMac($payload)) {
            throw new CryptException('The MAC is invalid.');
        }

        return $payload;
    }

    private function validatePayload(mixed $payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    private function validateMac(array $payload): bool
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['value']),
            $payload['mac']
        );
    }

    private function hash(string $iv, string $value): string
    {
        return hash_hmac($this->algo, $iv . $value, $this->getKey());
    }
}
