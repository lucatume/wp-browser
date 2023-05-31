<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\Random;

class ConfigurationData
{
    private ?string $authKey = null;
    private ?string $secureAuthKey = null;
    private ?string $loggedInKey = null;
    private ?string $nonceKey = null;
    private ?string $authSalt = null;
    private ?string $secureAuthSalt = null;
    private ?string $loggedInSalt = null;
    private ?string $nonceSalt = null;
    private ?string $extraPHP = null;
    /**
     * @var array<string,int|float|string|bool|null>
     */
    private array $extraConstants = [];

    /**
     * @param array<string,mixed> $array
     */
    public static function fromArray(array $array): ConfigurationData
    {
        $instance = new self;
        foreach ($array as $key => $value) {
            // From SOME_KEY to SomeKey
            $key = implode('', array_map('ucfirst', explode('_', strtolower($key))));
            $method = 'set' . $key;

            if (!method_exists($instance, $method)) {
                throw new \InvalidArgumentException("Invalid configuration key: {$key}");
            }

            $instance->{$method}($value);
        }
        return $instance;
    }

    public function setAuthKey(string $authKey): self
    {
        $this->authKey = $authKey;
        return $this;
    }

    public function getAuthKey(): string
    {
        if ($this->authKey === null) {
            $this->authKey = Random::salt(64);
        }
        return $this->authKey;
    }

    public function setSecureAuthKey(string $secureAuthKey): self
    {
        $this->secureAuthKey = $secureAuthKey;
        return $this;
    }

    public function getSecureAuthKey(): string
    {
        if ($this->secureAuthKey === null) {
            $this->secureAuthKey = Random::salt(64);
        }
        return $this->secureAuthKey;
    }

    public function setLoggedInKey(string $loggedInKey): self
    {
        $this->loggedInKey = $loggedInKey;
        return $this;
    }

    public function getLoggedInKey(): string
    {
        if ($this->loggedInKey === null) {
            $this->loggedInKey = Random::salt(64);
        }
        return $this->loggedInKey;
    }

    public function setNonceKey(string $nonceKey): self
    {
        $this->nonceKey = $nonceKey;
        return $this;
    }

    public function getNonceKey(): string
    {
        if ($this->nonceKey === null) {
            $this->nonceKey = Random::salt(64);
        }
        return $this->nonceKey;
    }

    public function setAuthSalt(string $authSalt): self
    {
        $this->authSalt = $authSalt;
        return $this;
    }

    public function getAuthSalt(): string
    {
        if ($this->authSalt === null) {
            $this->authSalt = Random::salt(64);
        }
        return $this->authSalt;
    }

    public function setSecureAuthSalt(string $secureAuthSalt): self
    {
        $this->secureAuthSalt = $secureAuthSalt;
        return $this;
    }

    public function getSecureAuthSalt(): string
    {
        if ($this->secureAuthSalt === null) {
            $this->secureAuthSalt = Random::salt(64);
        }
        return $this->secureAuthSalt;
    }

    public function setLoggedInSalt(string $loggedInSalt): self
    {
        $this->loggedInSalt = $loggedInSalt;
        return $this;
    }

    public function getLoggedInSalt(): string
    {
        if ($this->loggedInSalt === null) {
            $this->loggedInSalt = Random::salt(64);
        }
        return $this->loggedInSalt;
    }

    public function setNonceSalt(string $nonceSalt): self
    {
        $this->nonceSalt = $nonceSalt;
        return $this;
    }

    public function getNonceSalt(): string
    {
        if ($this->nonceSalt === null) {
            $this->nonceSalt = Random::salt(64);
        }
        return $this->nonceSalt;
    }

    /**
     * @return array{authKey: string, secureAuthKey: string, loggedInKey: string, nonceKey: string, authSalt: string,
     *                        secureAuthSalt: string, loggedInSalt: string, nonceSalt: string}
     */
    public function getSalts(): array
    {
        return [
            'authKey' => $this->getAuthKey(),
            'secureAuthKey' => $this->getSecureAuthKey(),
            'loggedInKey' => $this->getLoggedInKey(),
            'nonceKey' => $this->getNonceKey(),
            'authSalt' => $this->getAuthSalt(),
            'secureAuthSalt' => $this->getSecureAuthSalt(),
            'loggedInSalt' => $this->getLoggedInSalt(),
            'nonceSalt' => $this->getNonceSalt(),
        ];
    }

    public function setExtraPHP(string $extraPHP): self
    {
        $this->extraPHP = $extraPHP;
        return $this;
    }

    public function getExtraPHP(): ?string
    {
        $extraConstants = implode(
            PHP_EOL,
            array_map(
                static function ($const, string|int|float|bool|null $value) {
                    if (is_bool($value)) {
                        $value = $value ? 'true' : 'false';
                    } elseif (is_null($value)) {
                        $value = 'null';
                    } elseif (is_string($value)) {
                        $value = "'$value'";
                    }
                    return "define( '$const', $value );";
                },
                array_keys($this->extraConstants),
                $this->extraConstants
            )
        );
        return $extraConstants . PHP_EOL . $this->extraPHP;
    }

    public function setConst(string $const, string|int|float|bool|null $value): self
    {
        $this->extraConstants[$const] = $value;

        return $this;
    }
}
