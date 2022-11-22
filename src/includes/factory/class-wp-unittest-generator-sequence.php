<?php

class WP_UnitTest_Generator_Sequence {
	static $incr = -1;
	public $next;

	function __construct( public $template_string = '%s', $start = null ) {
		if ( $start ) {
			$this->next = $start;
		} else {
			self::$incr++;
			$this->next = self::$incr;
		}
	}

	function next() {
		$generated = sprintf( $this->template_string , $this->next );
		$this->next++;
		return $generated;
	}

	/**
	 * Get the incrementor.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	public function get_incr() {
		return self::$incr;
	}

	/**
	 * Get the template string.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public function get_template_string() {
		return $this->template_string;
	}
}
