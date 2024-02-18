<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPRestApi extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPRestApiTestCase
{
    {{tester}}
    public function setUp() :void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown() :void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function test_getting_posts() :void
    {
        // Create an editor.
        \$author_id = static::factory()->user->create( [ 'role' => 'author' ] );
        
        // Create and become editor.
        \$editor_id = static::factory()->user->create( [ 'role' => 'editor' ] );
        wp_set_current_user( \$editor_id );
       
        // Create 2 posts, one from the editor and one from the author.
        \$post_1_id = static::factory()->post->create( [ 'post_author' => \$editor_id ] );
		\$post_2_id = static::factory()->post->create( [ 'post_author' => \$author_id ] );

		// Get all posts in the database.
		\$request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		\$request->set_param( 'per_page', 10 );
		\$response = rest_get_server()->dispatch( \$request );
		\$this->assertSame( 200, \$response->get_status() );
		\$this->assertCount( 2, \$response->get_data() );

		// Exclude editor and author.
		\$request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		\$request->set_param( 'per_page', 10 );
		\$request->set_param( 'author_exclude', [ \$editor_id, \$author_id ] );
		\$response = rest_get_server()->dispatch( \$request );
		\$this->assertSame( 200, \$response->get_status() );
		\$data = \$response->get_data();
		\$this->assertCount( 0, \$data );

		// Exclude editor.
		\$request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		\$request->set_param( 'per_page', 10 );
		\$request->set_param( 'author_exclude', \$editor_id );
		\$response = rest_get_server()->dispatch( \$request );
		\$this->assertSame( 200, \$response->get_status() );
		\$data = \$response->get_data();
		\$this->assertCount( 1, \$data );
		\$this->assertNotEquals( \$editor_id, \$data[0]['author'] );

		// Invalid 'author_exclude' should error.
		\$request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		\$request->set_param( 'author_exclude', 'invalid' );
		\$response = rest_get_server()->dispatch( \$request );
		\$this->assertErrorResponse( 'rest_invalid_param', \$response, 400 );
    }
}

EOF;
}
