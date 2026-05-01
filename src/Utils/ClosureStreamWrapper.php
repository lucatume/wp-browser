<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Stream wrapper methods require snake_case names.
final class ClosureStreamWrapper
{
    private int $position = 0;

    private string $content = '';

    /**
     * @var resource|null
     */
    public $context;

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $sep = strpos($path, '://');
        if ($sep === false) {
            return false;
        }
        $payload = substr($path, $sep + 3);

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return false;
        }

        $this->content = "<?php\n" . $decoded;
        $this->position = 0;

        return true;
    }

    public function stream_read(int $count): string
    {
        $ret = substr($this->content, $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    /**
     * @return array<string, int>
     */
    public function stream_stat(): array
    {
        return [
            'size' => strlen($this->content),
            'mode' => 0100644,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function url_stat(string $path, int $flags): array
    {
        return [
            'size' => strlen($this->content),
            'mode' => 0100644,
        ];
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        switch ($whence) {
            case SEEK_SET:
                $this->position = $offset;
                break;
            case SEEK_CUR:
                $this->position += $offset;
                break;
            case SEEK_END:
                $this->position = strlen($this->content) + $offset;
                break;
        }

        return true;
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_set_option(int $option, int $arg1, ?int $arg2): bool
    {
        return true;
    }
}
// phpcs:enable
