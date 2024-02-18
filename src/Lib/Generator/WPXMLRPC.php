<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPXMLRPC extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPXMLRPCTestCase
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

    public function test_date_edit_comment() :void
    {
        \$timezone = 'Europe/Helsinki';
		update_option( 'timezone_string', \$timezone );

		\$datetime    = new \DateTimeImmutable( 'now', new DateTimeZone( \$timezone ) );
		\$datetime    = \$datetime->modify( '-1 hour' );
		\$datetimeutc = \$datetime->setTimezone( new DateTimeZone( 'UTC' ) );

		\$this->make_user_by_role( 'administrator' );
		\$post_id = static::factory()->post->create();

		\$comment_data = [
			'comment_post_ID'      => \$post_id,
			'comment_author'       => 'Test commenter',
			'comment_author_url'   => 'http://example.com/',
			'comment_author_email' => 'example@example.com',
			'comment_content'      => 'Hello, world!',
			'comment_approved'     => '1',
		];
		\$comment_id   = wp_insert_comment( \$comment_data );

		\$result = \$this->myxmlrpcserver->wp_editComment(
			[
				1,
				'administrator',
				'administrator',
				\$comment_id,
				[
					'date_created_gmt' => new IXR_Date( \$datetimeutc->format( 'Ymd\TH:i:s' ) ),
				],
			]
		);

		\$fetched_comment = get_comment( \$comment_id );

		\$this->assertTrue( \$result );
		\$this->assertSame(
			\$datetime->format( 'Y-m-d H:i:s' ),
			\$fetched_comment->comment_date,
			'UTC time into wp_editComment'
		);
    }
}

EOF;
}
