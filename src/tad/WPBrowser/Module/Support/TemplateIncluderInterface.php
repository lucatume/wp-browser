<?php

namespace tad\WPBrowser\Module\Support;

interface TemplateIncluderInterface
{
    public function getInterceptedTemplatesList();

    public function gotHeader($header = '');

    public function gotFooter($footer = '');

    public function gotSidebar($sidebar = '');

    public function includeTemplate($template);

    public function gotTemplate($template);

    public function interceptTemplate($templateType, $templateBasenameRegex);

    public function isIntercepting($templateType);

    public function lastIncludedTemplateType();

    public function resetInclusions();

    public function resetInclusionForTemplateType($string);

    public function getHeader($header = '');

    public function getFooter($footer = '');

    public function getSidebar($sidebar = '');
}