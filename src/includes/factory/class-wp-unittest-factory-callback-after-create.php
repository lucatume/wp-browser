<?php

class WP_UnitTest_Factory_Callback_After_Create {
	function __construct($callback)
 {
 }

	function call( $object ) {
		return call_user_func( $this->callback, $object );
	}
}
