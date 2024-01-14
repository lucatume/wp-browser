<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPCanonical extends AbstractGenerator
{
    protected string $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPCanonicalTestCase
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

    public function test_custom_rewrite_rule() :void
    {
        global \$wp_rewrite;
		// Add a custom Rewrite rule to test category redirections.
		\$wp_rewrite->add_rule( 
		    'ccr/(.+?)/sort/(asc|desc)',
		    'index.php?category_name=\$matches[1]&order=\$matches[2]',
		    'top'
        );
		\$wp_rewrite->flush_rules();    
		
	    \$this->assertCanonical( 
            '/ccr/test-category/sort/asc/',
            [
                'url' => '/ccr/test-category/sort/asc/',
                'qv'  => [
                    'category_name' => 'test-category',
                    'order'         => 'asc',
                ],
             ]
        );
	}
}

EOF;
}
