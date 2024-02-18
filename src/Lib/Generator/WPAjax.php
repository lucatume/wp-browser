<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPAjax extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPAjaxTestCase
{
    {{tester}}
    public function setUp() :void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
        add_action('wp_ajax_add-meta', function () {
            wp_verify_nonce(\$_POST['_ajax_nonce-add-meta'], 'add-meta');
            \$post_id = \$_POST['post_id'];
            \$key = \$_POST['key'];
            \$value = \$_POST['value'];
            update_post_meta(\$post_id, \$key, \$value);
        });
    }

    public function tearDown() :void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function test_ajax_call_handling() :void
    {
        \$post = self::factory()->post->create();

		add_post_meta( \$post, 'testkey', 'initial_value' );

		// Become an administrator.
		\$this->_setRole( 'administrator' );

        \$_POST = [
            '_ajax_nonce-add-meta' => wp_create_nonce('add-meta'),
            'post_id' => \$post,
            'key' => 'testkey',
            'value' => 'updated_value',
        ];

		// Make the request.
		try {
			\$this->_handleAjax( 'add-meta' );
		} catch ( WPAjaxDieContinueException \$e ) {
			unset( \$e );
		}

		\$this->assertSame( 'updated_value', get_post_meta( \$post, 'testkey', true ) );
    }
}

EOF;
}
