<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPXML extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPXMLTestCase
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

    public function test_get_sitemap_index_xml() :void
    {
        \$entries = [
			[
				'loc' => 'http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-posts-post-1.xml',
			],
			[
				'loc' => 'http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-posts-page-1.xml',
			],
			[
				'loc' => 'http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-taxonomies-category-1.xml',
			],
			[
				'loc' => 'http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-taxonomies-post_tag-1.xml',
			],
			[
				'loc' => 'http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-users-1.xml',
			],
		];

		\$renderer = new \WP_Sitemaps_Renderer();

		\$actual   = \$renderer->get_sitemap_index_xml( \$entries );
		\$expected = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<?xml-stylesheet type="text/xsl" href="http://' . \WP_TESTS_DOMAIN . '/?sitemap-stylesheet=index" ?>' .
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
            '<sitemap><loc>http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-posts-post-1.xml</loc></sitemap>' .
            '<sitemap><loc>http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-posts-page-1.xml</loc></sitemap>' .
            '<sitemap><loc>http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-taxonomies-category-1.xml</loc></sitemap>' .
            '<sitemap><loc>http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-taxonomies-post_tag-1.xml</loc></sitemap>' .
            '<sitemap><loc>http://' . \WP_TESTS_DOMAIN . '/wp-sitemap-users-1.xml</loc></sitemap>' .
            '</sitemapindex>';

		\$this->assertXMLEquals( \$expected, \$actual, 'Sitemap index markup incorrect.' );
    }
}

EOF;
}
