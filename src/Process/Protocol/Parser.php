<?php

namespace lucatume\WPBrowser\Process\Protocol;

class Parser
{
    /**
     * @throws ProtocolException
     */
    public static function decode(string $input, ?int $offset = 0, ?int $count = null): array
    {
        if ($offset < 0) {
            throw new ProtocolException('Decode offset cannot be negative', ProtocolException::DECODE_NEGATIVE_OFFSET);
        }

        if (empty($input)) {
            throw new ProtocolException('Empty input', ProtocolException::EMPTY_INPUT);
        }

        $firstChar = $input[0];

        if ($firstChar !== '$') {
            throw new ProtocolException('Missing start char', ProtocolException::MISSING_START_CHAR);
        }

        if (!str_ends_with($input, "\r\n")) {
            throw new ProtocolException('Missing ending CRLF', ProtocolException::MISSING_ENDING_CRLF);
        }

        $chunks = [];
        $pos = 0;
        $stopPos = $offset + $count - 1;
        $inputLen = strlen($input);
        $i = 1;
        do {
            $length = 0;

            for (; $i < $inputLen && $input[$i] !== "\r"; $i++) {
                $digit = $input[$i];
                if (!is_numeric($digit)) {
                    throw new ProtocolException('Non numeric length', ProtocolException::NON_NUMERIC_LENGTH);
                }
                $length = $length * 10 + (int)$digit;
            }

            if ($pos < $offset) {
                // Skip the chunk.
                goto chunkProcessed;
            }

            if ($length === 0) {
                $chunks[] = '';
                goto chunkProcessed;
            }

            $content = substr($input, $i + 2, $length);

            if (strlen($content) !== $length) {
                throw new ProtocolException('Invalid length', ProtocolException::MISMATCHING_LENGTH);
            }

            $decoded = @unserialize(base64_decode($content), ['allowed_classes' => true]);
            unset($content);

            if ($decoded === false) {
                throw new ProtocolException('Content not correctly encoded', ProtocolException::INCORRECT_ENCODING);
            }

            $chunks[] = $decoded;

            chunkProcessed:
            if ($count !== null && $pos === $stopPos) {
                break;
            }
            $i += $length + 4;
            ++$pos;
        } while ($i < $inputLen);

        return $chunks;
    }

    public static function encode(array $input): string
    {
        $output = '$';
        foreach ($input as $chunk) {
            $serialized = $chunk !== '' ? base64_encode(serialize($chunk)) : '';
            $output .= strlen($serialized) . "\r\n" . $serialized . "\r\n";
        }

        return $output;
    }
}
