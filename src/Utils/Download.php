<?php

namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\Exceptions\RuntimeException;

class Download
{

    /**
     * @throws RuntimeException
     */
    public static function fileFromUrl(
        string $sourceUrl,
        string $destinationPath,
        bool $verifyHost = true
    ): string {
        codecept_debug("Downloading file $sourceUrl ...");

        $file = fopen($destinationPath, 'wb');

        if (!is_resource($file)) {
            throw new RuntimeException(
                "File $sourceUrl download failed: could not open destination file to write."
            );
        }

        $curlHandle = curl_init();

        if ($curlHandle === false) {
            fclose($file);

            throw new RuntimeException("File $sourceUrl download failed: could not initialize cURL.");
        }

        curl_setopt($curlHandle, CURLOPT_URL, $sourceUrl);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_AUTOREFERER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 120);
        curl_setopt($curlHandle, CURLOPT_FILE, $file);

        if (!$verifyHost) {
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        }

        if (!(curl_exec($curlHandle))) {
            throw new RuntimeException("File $sourceUrl download failed: " . curl_error($curlHandle));
        }

        // This will fclose as well.
        curl_close($curlHandle);

        codecept_debug("File $sourceUrl downloaded.");

        return $destinationPath;
    }
}
