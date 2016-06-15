<?php
namespace tad\WPBrowser\Module\Support;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prophecy\Argument;
use tad\WPBrowser\Adapters\WP;

class TemplateIndcluderTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * @var WP
     */
    protected $wp;

    protected function _before()
    {
        $this->root = vfsStream::setup();

        $header = vfsStream::newFile('header.php');
        $header->setContent('header');
        $footer = vfsStream::newFile('footer.php');
        $footer->setContent('footer');
        $sidebar = vfsStream::newFile('sidebar.php');
        $sidebar->setContent('sidebar');

        $headerSecondary = vfsStream::newFile('header-secondary.php');
        $headerSecondary->setContent('header');
        $footerSecondary = vfsStream::newFile('footer-secondary.php');
        $footerSecondary->setContent('footer');
        $sidebarSecondary = vfsStream::newFile('sidebar-secondary.php');
        $sidebarSecondary->setContent('sidebar');

        $anotherTemplate = vfsStream::newFile('another-template.php');
        $anotherTemplate->setContent('another template');

        $templatesDir = vfsStream::newDirectory('templates');
        $templatesDir->addChild($header);
        $templatesDir->addChild($footer);
        $templatesDir->addChild($sidebar);
        $templatesDir->addChild($headerSecondary);
        $templatesDir->addChild($footerSecondary);
        $templatesDir->addChild($sidebarSecondary);
        $templatesDir->addChild($anotherTemplate);

        $this->root->addChild($templatesDir);

        $this->wp = $this->prophesize(WP::class);
        $root = $this->root->url() . '/templates/';
        $this->wp->locate_template(Argument::type('string'), false)->will(function ($args) use ($root) {
            return $root . $args[0];
        });
    }

    protected function _after()
    {
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(TemplateIncluder::class, $sut);
    }

    /**
     * @test
     * it should intercept calls to include header, footer and sidebar by default
     */
    public function it_should_intercept_calls_to_include_header_footer_and_sidebar_by_default()
    {
        $sut = $this->make_instance();
        $this->assertArrayHasKey('header', $sut->getInterceptedTemplatesList());
        $this->assertArrayHasKey('footer', $sut->getInterceptedTemplatesList());
        $this->assertArrayHasKey('sidebar', $sut->getInterceptedTemplatesList());
    }

    /**
     * @test
     * it should initialize with header, footer and sidebar set to not included
     */
    public function it_should_initialize_with_header_footer_and_sidebar_set_to_not_included()
    {
        $sut = $this->make_instance();

        $this->assertFalse($sut->gotHeader());
        $this->assertFalse($sut->gotFooter());
        $this->assertFalse($sut->gotSidebar());
    }

    /**
     * @test
     * it should  initialized with custom header, footer and sidebar not included
     */
    public function it_should_initialized_with_custom_header_footer_and_sidebar_not_included()
    {
        $sut = $this->make_instance();

        $this->assertFalse($sut->gotHeader('foo'));
        $this->assertFalse($sut->gotFooter('foo'));
        $this->assertFalse($sut->gotSidebar('foo'));
    }

    /**
     * @test
     * it should mark header, footer and sidebar as included when included
     */
    public function it_should_mark_header_footer_and_sidebar_as_included_when_included()
    {
        $sut = $this->make_instance();

        $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertTrue($sut->gotHeader());

        $sut->includeTemplate($this->root->url() . '/templates/footer.php');

        $this->assertTrue($sut->gotFooter());

        $sut->includeTemplate($this->root->url() . '/templates/sidebar.php');

        $this->assertTrue($sut->gotSidebar());
    }

    /**
     * @test
     * it should mark secondary header, footer and sidebar as included
     */
    public function it_should_mark_secondary_header_footer_and_sidebar_as_included()
    {
        $sut = $this->make_instance();

        $sut->includeTemplate($this->root->url() . '/templates/header-secondary.php');

        $this->assertTrue($sut->gotHeader('secondary'));

        $sut->includeTemplate($this->root->url() . '/templates/footer-secondary.php');

        $this->assertTrue($sut->gotFooter('secondary'));

        $sut->includeTemplate($this->root->url() . '/templates/sidebar-secondary.php');

        $this->assertTrue($sut->gotSidebar('secondary'));
    }

    /**
     * @test
     * it should not intercept other templates
     */
    public function it_should_not_intercept_other_templates()
    {
        $sut = $this->make_instance();

        $sut->includeTemplate($this->root->url() . '/templates/another-template.php');

        $this->assertFalse($sut->gotTemplate('another-template'));
    }

    /**
     * @test
     * it should allow adding templates that should be intercepted
     */
    public function it_should_allow_adding_templates_that_should_be_intercepted()
    {
        $sut = $this->make_instance();

        $sut->interceptTemplate('another-template', '/another-template/');

        $sut->includeTemplate($this->root->url() . '/templates/another-template.php');

        $this->assertTrue($sut->gotTemplate('another-template'));
    }

    /**
     * @test
     * it should allow verifying a template is being interecepted
     */
    public function it_should_allow_verifying_a_template_is_being_interecepted()
    {
        $sut = $this->make_instance();

        $sut->interceptTemplate('another-template', '/another-template/');

        $this->assertTrue($sut->isIntercepting('header'));
        $this->assertTrue($sut->isIntercepting('another-template'));
        $this->assertFalse($sut->isIntercepting('foo-template'));
    }

    /**
     * @test
     * it should return the template if template is not being intercepted
     */
    public function it_should_return_the_template_if_template_is_not_being_intercepted()
    {
        $sut = $this->make_instance();
        $toInclude = $this->root->url() . '/templates/another-template.php';

        $template = $sut->includeTemplate($toInclude);

        $this->assertEquals($template, $toInclude);
    }

    /**
     * @test
     * it should return false when intercepting template not included already
     */
    public function it_should_return_false_when_intercepting_template_not_included_already()
    {
        $sut = $this->make_instance();

        $template = $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertFalse($template);
        $this->assertEquals('header', $sut->lastIncludedTemplateType());

        $template = $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertFalse($template);
        $this->assertFalse($sut->lastIncludedTemplateType());
    }

    /**
     * @test
     * it should allow resetting template inclusion records
     */
    public function it_should_allow_resetting_template_inclusion_records()
    {
        $sut = $this->make_instance();

        $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertTrue($sut->gotHeader());

        $sut->resetInclusions();

        $this->assertFalse($sut->gotHeader());

        $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertTrue($sut->gotHeader());
    }

    /**
     * @test
     * it should allow resetting template inclusion records for custom templates
     */
    public function it_should_allow_resetting_template_inclusion_records_for_custom_templates()
    {
        $sut = $this->make_instance();

        $sut->interceptTemplate('another-template', '/another-template/');
        $sut->includeTemplate($this->root->url() . '/templates/another-template.php');

        $this->assertTrue($sut->gotTemplate('another-template'));

        $sut->resetInclusions();

        $this->assertFalse($sut->gotTemplate('another-template'));

        $sut->includeTemplate($this->root->url() . '/templates/another-template.php');

        $this->assertTrue($sut->gotTemplate('another-template'));
    }

    /**
     * @test
     * it should allow resetting single template inclusion record
     */
    public function it_should_allow_resetting_single_template_inclusion_record()
    {
        $sut = $this->make_instance();

        $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertTrue($sut->gotHeader());

        $sut->resetInclusionForTemplateType('header');

        $this->assertFalse($sut->gotHeader());

        $sut->includeTemplate($this->root->url() . '/templates/header.php');

        $this->assertTrue($sut->gotHeader());
    }

    /**
     * @test
     * it should allow resetting single custom templates inclusion records
     */
    public function it_should_allow_resetting_single_custom_templates_inclusion_records()
    {
        $sut = $this->make_instance();

        $sut->interceptTemplate('another-template', '/another-template/');
        $sut->includeTemplate($this->root->url() . '/templates/another-template.php');

        $this->assertTrue($sut->gotTemplate('another-template'));

        $sut->resetInclusionForTemplateType('another-template');

        $this->assertFalse($sut->gotTemplate('another-template'));

        $sut->includeTemplate($this->root->url() . '/templates/another-template.php');

        $this->assertTrue($sut->gotTemplate('another-template'));
    }

    /**
     * @test
     * it should intercept get_header action to load header once
     */
    public function it_should_intercept_get_header_action_to_load_header_once()
    {
        $sut = $this->make_instance();

        $this->assertTrue($sut->getHeader());
        $this->assertFalse($sut->getHeader());

        $sut->resetInclusions();

        $this->assertTrue($sut->getHeader());
        $this->assertFalse($sut->getHeader());
    }

    /**
     * @test
     * it should intercept get_footer action to load footer once
     */
    public function it_should_intercept_get_footer_action_to_load_footer_once()
    {
        $sut = $this->make_instance();

        $this->assertTrue($sut->getFooter());
        $this->assertFalse($sut->getFooter());

        $sut->resetInclusions();

        $this->assertTrue($sut->getFooter());
        $this->assertFalse($sut->getFooter());
    }

    /**
     * @test
     * it should intercept get_sidebar action to load sidebar once
     */
    public function it_should_intercept_get_sidebar_action_to_load_sidebar_once()
    {
        $sut = $this->make_instance();

        $this->assertTrue($sut->getSidebar());
        $this->assertFalse($sut->getSidebar());

        $sut->resetInclusions();

        $this->assertTrue($sut->getSidebar());
        $this->assertFalse($sut->getSidebar());
    }

    /**
     * @test
     * it should intercept get_header action to load secondary header once
     */
    public function it_should_intercept_get_header_action_to_load_secondary_header_once()
    {
        $sut = $this->make_instance();

        $this->assertTrue($sut->getHeader('secondary'));
        $this->assertFalse($sut->getHeader('secondary'));

        $sut->resetInclusions();

        $this->assertTrue($sut->getHeader('secondary'));
        $this->assertFalse($sut->getHeader('secondary'));
    }

    /**
     * @test
     * it should intercept get_footer action to load secondary footer once
     */
    public function it_should_intercept_get_footer_action_to_load_secondary_footer_once()
    {
        $sut = $this->make_instance();

        $this->assertTrue($sut->getFooter('secondary'));
        $this->assertFalse($sut->getFooter('secondary'));

        $sut->resetInclusions();

        $this->assertTrue($sut->getFooter('secondary'));
        $this->assertFalse($sut->getFooter('secondary'));
    }

    /**
     * @test
     * it should intercept get_sidebar action to load secondary sidebar once
     */
    public function it_should_intercept_get_sidebar_action_to_load_secondary_sidebar_once()
    {
        $sut = $this->make_instance();

        $this->assertTrue($sut->getSidebar('secondary'));
        $this->assertFalse($sut->getSidebar('secondary'));

        $sut->resetInclusions();

        $this->assertTrue($sut->getSidebar('secondary'));
        $this->assertFalse($sut->getSidebar('secondary'));
    }

    /**
     * @return TemplateIncluderInterface
     */
    private function make_instance()
    {
        return new TemplateIncluder($this->wp->reveal());
    }
}