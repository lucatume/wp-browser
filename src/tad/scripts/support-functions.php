<?php
global $cli_headers, $cli_statuses;
/**
 * An associative array in the [header => status] format.
 */
$cli_headers = [];

/**
 * An array of statuses in the format `HTTP/1.1 200 Ok`
 */
$cli_statuses = [];

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
	global $cli_statuses;
	if (empty($cli_statuses)) {
		return 200;
	}

	return end($cli_statuses);
}


