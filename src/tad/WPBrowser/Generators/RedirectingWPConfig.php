<?php

namespace tad\WPBrowser\Generators;

use Handlebars\Handlebars;

class RedirectingWPConfig implements TemplateProviderInterface
{

    protected $template = <<< PHP
<?php
\$options = array(
	'subdomainInstall' => {{subdomainInstall}},
	'siteDomain' => "{{siteDomain}}"
);
\$multisiteConstants = array(
	'WP_ALLOW_MULTISITE' => true,
	'MULTISITE' => true,
	'SUBDOMAIN_INSTALL' => \$options['subdomainInstall'],
	'DOMAIN_CURRENT_SITE' => \$options['siteDomain'],
	'PATH_CURRENT_SITE' => '/',
	'SITE_ID_CURRENT_SITE' => 1,
	'BLOG_ID_CURRENT_SITE' => 1
);
foreach (\$multisiteConstants as \$multisiteConstant => \$value) {
	if (!defined(\$multisiteConstant)) {
		define(\$multisiteConstant, \$value);
	}
}
\$original = dirname(__FILE__) . '/original-wp-config.php';
if(file_exists(\$original)){
	include \$original;
} else {
	die("origina-wp-config.php file not found in '\$original'");
}
PHP;

    /**
     * @var Handlebars
     */
    private $handlebars;
    /**
     * @var array
     */
    private $data;

    public function __construct(Handlebars $handlebars, array $data = [ ])
    {
        $this->handlebars = $handlebars;
        $this->data       = $data;
    }

    public function getContents()
    {
        return $this->handlebars->render($this->template, $this->data);
    }
}
