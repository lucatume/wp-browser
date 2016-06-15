<?php

namespace tad\WPBrowser\Module\Support;


use tad\WPBrowser\Adapters\WP;

class TemplateIncluder implements TemplateIncluderInterface
{

    /**
     * @var array
     */
    protected $interceptedTemplatesList = [
        'header' => '/^header(-[-_A-Za-z0-9]+)*/',
        'footer' => '/^footer(-[-_A-Za-z0-9]+)*/',
        'sidebar' => '/^sidebar(-[-_A-Za-z0-9]+)*/'
    ];

    /**
     * @var array
     */
    protected $included = [];

    /**
     * @var bool
     */
    protected $lastIncludedTemplateType = false;

    /**
     * @var WP
     */
    protected $wp;

    /**
     * TemplateIncluder constructor.
     * @param WP $wp
     */
    public function __construct(WP $wp = null)
    {
        $this->wp = $wp ? $wp : new WP();
    }

    public function getInterceptedTemplatesList()
    {
        return $this->interceptedTemplatesList;
    }

    public function gotTemplate($template)
    {
        return isset($this->included[$template]);
    }

    public function interceptTemplate($templateType, $templateBasenameRegex)
    {
        $this->interceptedTemplatesList[$templateType] = $templateBasenameRegex;
    }

    public function isIntercepting($templateType)
    {
        return isset($this->interceptedTemplatesList[$templateType]);
    }

    public function lastIncludedTemplateType()
    {
        return $this->lastIncludedTemplateType;
    }

    public function resetInclusions()
    {
        $this->included = [];
    }

    public function resetInclusionForTemplateType($string)
    {
        if (!isset($this->included[$string])) {
            return;
        }

        $this->included = array_diff_key($this->included, [$string => $string]);
    }

    public function getHeader($header = '')
    {
        if ($this->gotHeader($header)) {
            return false;
        }

        $header = $header ? 'header-' . $header : 'header';
        $located = $this->wp->locate_template($header . '.php', false);

        $this->includeTemplate($located);

        return true;
    }

    public function gotHeader($header = '')
    {
        $key = empty($header) ? 'header' : 'header-' . $header;
        return isset($this->included[$key]);
    }

    public function includeTemplate($template)
    {
        $templateBasename = basename($template, '.php');
        $templateType = $this->matchTemplateType($templateBasename);

        if (empty($templateType)) {
            return $template;
        }

        if (isset($this->included[$templateType])) {
            $this->lastIncludedTemplateType = false;
        } else {
            $this->included[$templateType] = $template;
            $this->lastIncludedTemplateType = $templateType;
            require $template;
        }


        return false;
    }

    /**
     * @param $templateBasename
     * @return int|string
     */
    private function matchTemplateType($templateBasename)
    {
        foreach ($this->interceptedTemplatesList as $type => $basenamePattern) {
            if (preg_match($basenamePattern, $templateBasename)) {
                return $type . str_replace($type, '', $templateBasename);
            }
        }

        return false;
    }

    public function getFooter($footer = '')
    {
        if ($this->gotFooter($footer)) {
            return false;
        }

        $footer = $footer ? 'footer-' . $footer : 'footer';
        $located = $this->wp->locate_template($footer . '.php', false);

        $this->includeTemplate($located);

        return true;
    }

    public function gotFooter($footer = '')
    {
        $key = empty($footer) ? 'footer' : 'footer-' . $footer;
        return isset($this->included[$key]);
    }

    public function getSidebar($sidebar = '')
    {
        if ($this->gotSidebar($sidebar)) {
            return false;
        }

        $sidebar = $sidebar ? 'sidebar-' . $sidebar : 'sidebar';
        $located = $this->wp->locate_template($sidebar . '.php', false);

        $this->includeTemplate($located);

        return true;
    }

    public function gotSidebar($sidebar = '')
    {
        $key = empty($sidebar) ? 'sidebar' : 'sidebar-' . $sidebar;
        return isset($this->included[$key]);
    }
}