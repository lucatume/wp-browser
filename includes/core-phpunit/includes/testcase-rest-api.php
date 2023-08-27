<?php

namespace lucatume\WPBrowser\TestCase;

abstract class WPRestApiTestCase extends WPTestCase {
	protected function assertErrorResponse( $code, $response, $status = null ) {

		if ( $response instanceof WP_REST_Response ) {
			$response = $response->as_error();
		}

		$this->assertWPError( $response );
		$this->assertSame( $code, $response->get_error_code() );

		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertArrayHasKey( 'status', $data );
			$this->assertSame( $status, $data['status'] );
		}
	}
}
