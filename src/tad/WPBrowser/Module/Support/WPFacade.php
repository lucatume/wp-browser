<?php

namespace tad\WPBrowser\Module\Support;


use Codeception\Module\WPLoader;
use tad\WPBrowser\Adapters\WP;

class WPFacade implements WPFacadeInterface
{
    /**
     * @var WPLoader
     */
    protected $loader;

    /**
     * @var WP
     */
    protected $wpAdapter;

    /**
     * @var array
     */
    protected $config;
    /**
     * @var TemplateIncluderInterface
     */
    protected $templateIncluder;
    /**
     * @var WpDieHandlerInterface
     */
    private $dieHandler;

    public function __construct(WPLoader $loader, array $config = [], WP $wpAdapter = null, TemplateIncluderInterface $templateIncluder = null, WpDieHandlerInterface $dieHandler = null)
    {
        $this->loader = $loader;
        $this->config = $config;
        $this->wpAdapter = $wpAdapter ? $wpAdapter : new WP();
        $this->templateIncluder = $templateIncluder ? $templateIncluder : new TemplateIncluder($this->wpAdapter);
        $this->dieHandler = $dieHandler ? $dieHandler : new WpDieHandler();
    }

    public function initialize()
    {
        $this->loader->_initialize();
    }

    public function home_url($path = '', $scheme = null)
    {
        return $this->wpAdapter->home_url($path, $scheme);
    }

    public function admin_url($path = '', $scheme = 'admin')
    {
        return $this->wpAdapter->admin_url($path, $scheme);
    }

    public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return $this->wpAdapter->add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return $this->wpAdapter->add_action($tag, $function_to_add, $priority, $accepted_args);
    }

    public function getTemplateIncluder()
    {
        return $this->templateIncluder;
    }

    public function resetInclusions()
    {
        $this->templateIncluder->resetInclusions();
    }

    public function update_option($option, $new_value, $autoload = null)
    {
        return $this->wpAdapter->update_option($option, $new_value, $autoload);
    }

    public function flush_rewrite_rules($hard = true)
    {
        $this->wpAdapter->flush_rewrite_rules($hard);
    }

    public function includeTemplate($template)
    {
        return $this->templateIncluder->includeTemplate($template);
    }

    public function getHeader($header)
    {
        return $this->templateIncluder->getHeader($header);
    }

    public function getFooter($footer)
    {
        return $this->templateIncluder->getFooter($footer);
    }

    public function getSidebar($sidebar)
    {
        return $this->templateIncluder->getSidebar($sidebar);
    }

    public function handleAjaxDie()
    {
        return $this->dieHandler->handleAjaxDie();
    }

    public function handleXmlrpcDie()
    {
        return $this->dieHandler->handleXmlrpcDie();
    }

    public function handleDie()
    {
        return $this->dieHandler->handleDie();
    }

    public function getAdminPath()
    {
        return rtrim(str_replace($this->wpAdapter->home_url(), '', $this->wpAdapter->admin_url()), '/');
    }
}
