<?php

namespace lucatume\WPBrowser\Process\Protocol;

use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Opis\Closure\SerializableClosure;

class Request
{
    private Control $control;
    private bool $useFilePayloads = false;

    /**
     * @param array{
     *     autoloadFile?: ?string,
     *     requireFiles?: string[],
     *     cwd?: string|false,
     *     codeceptionRootDir?: string,
     *     codeceptionConfig?: array<string, mixed>,
     *     composerAutoloadPath?: ?string,
     *     composerBinDir?: ?string,
     *     env?: array<string, string|int|float|bool>
     * } $controlArray
     * @throws ConfigurationException
     */
    public function __construct(array $controlArray, private SerializableClosure $serializableClosure)
    {
        $this->control = new Control($controlArray);
    }

    /**
     * @throws ProtocolException
     */
    public function getPayload(): string
    {
        $payload = Parser::encode([$this->control->toArray(), $this->serializableClosure]);

        if (DIRECTORY_SEPARATOR === '\\' || $this->useFilePayloads) {
            // On Windows the maximum length of the command line is 8191 characters.
            // Any expanded env var, any path (e.g. to the PHP binary or the worker script) counts towards that limit.
            // To avoid running into that limit we pass the payload through a temp file.
            $payloadFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('wpb_worker_payload_', true);

            if (file_put_contents($payloadFile, $payload, LOCK_EX) === false) {
                throw new ProtocolException("Could not write payload to file $payloadFile");
            }

            return $payloadFile;
        }

        return $payload;
    }

    /**
     * @throws ProtocolException|ConfigurationException
     */
    public static function fromPayload(string $payload): self
    {
        // Decode only the control now to decode the rest when auto-loading is working.
        [$controlArray] = Parser::decode($payload, 0, 1);

        if (!is_array($controlArray)) {
            throw new ProtocolException('Decoded control is not an array.');
        }

        $control = new Control($controlArray);
        $control->apply();

        [$serializableClosure] = Parser::decode($payload, 1, 1);

        if (!$serializableClosure instanceof SerializableClosure) {
            throw new ProtocolException('Decoded closure is not an instance of SerializableClosure.');
        }

        return new self($controlArray, $serializableClosure);
    }

    public function getSerializableClosure(): SerializableClosure
    {
        return $this->serializableClosure;
    }

    public function getControl(): Control
    {
        return clone $this->control;
    }

    public function setUseFilePayloads(bool $useFilePayloads): Request
    {
        $this->useFilePayloads = $useFilePayloads;

        return $this;
    }
}
