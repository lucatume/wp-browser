<?php
/**
 * A marker function as the file might be loaded from multiple plugins under or involved in the tests.
 */
function tad_functions() {
}

/**
 * @param callable $callback A callable.
 * @param array $whitelist An array of filters that should remain hooked.
 *
 * @return mixed The callback return value.
 */
function _without_filters( $callback, $whitelist = array() ) {
	if ( ! is_callable( $callback ) ) {
		throw new InvalidArgumentException( 'Callback must be callable' );
	}

	global $wp_filter, $merged_filters;

	// Save filters and actions state
	$wp_filter_backup      = $wp_filter;
	$merged_filters_backup = $merged_filters;

	$whitelist = array_combine( $whitelist, $whitelist );
	$wp_filter = array_intersect_key( $wp_filter, $whitelist );
	if ( ! empty( $merged_filters ) ) {
		$merged_filters = array_intersect_key( $merged_filters, $whitelist );
	}

	$exit = call_user_func( $callback );

	// Restore previous state
	$wp_filter = $wp_filter_backup;
	if ( ! empty( $merged_filters_backup ) ) {
		$merged_filters = $merged_filters_backup;
	}

	return $exit;
}
