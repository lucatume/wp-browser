<?php
global $cli_headers;
/**
 * An associative array in the [header => status] format.
 */
$cli_headers = [];

/**
 * Copy and paste of WordPress original function where headers are but stored
 * before sending to avoid CLI limitations.
 *
 * @param $location
 * @param int $status
 * @return bool
 */
function wp_redirect($location, $status = 302)
{
    global $is_IIS;

    /**
     * Filter the redirect location.
     *
     * @since 2.1.0
     *
     * @param string $location The path to redirect to.
     * @param int $status Status code to use.
     */
    $location = apply_filters('wp_redirect', $location, $status);

    /**
     * Filter the redirect status code.
     *
     * @since 2.3.0
     *
     * @param int $status Status code to use.
     * @param string $location The path to redirect to.
     */
    $status = apply_filters('wp_redirect_status', $status, $location);

    if (!$location)
        return false;

    $location = wp_sanitize_redirect($location);

    if (!$is_IIS && PHP_SAPI != 'cgi-fcgi')
        status_header($status); // This causes problems on IIS and some FastCGI setups

    header("Location: $location", true, $status);

    global $cli_headers;
    $cli_headers["Location: $location"] = $status;

    return true;
}

function cli_headers_list()
{
    $headers = [];
    global $cli_headers;
    $php_headers = array_keys($cli_headers);
    foreach ($php_headers as $value) {
        // Get the header name
        $parts = explode(':', $value);
        if (count($parts) > 1) {
            $name = trim(array_shift($parts));
            // Build the header hash map
            $headers[$name] = trim(implode(':', $parts));
        }
    }
    $headers['Content-type'] = isset($headers['Content-type'])
        ? $headers['Content-type']
        : "text/html; charset=UTF-8";

    return $headers;
}

function cli_headers_last_status()
{
    global $cli_headers;
    if (empty($cli_headers)) {
        return 200;
    }

    return end($cli_headers);
}
