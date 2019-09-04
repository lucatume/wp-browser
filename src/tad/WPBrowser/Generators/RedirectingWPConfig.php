<?php

namespace tad\WPBrowser\Generators;

use function tad\WPBrowser\renderString;

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
     * @var array
     */
    private $data;

    public function __construct(array $data = [ ])
    {
        $this->data       = $data;
    }

    public function getContents()
    {
        return renderString($this->template, $this->data);
    }
}
