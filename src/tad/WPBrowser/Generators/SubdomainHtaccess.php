<?php

namespace tad\WPBrowser\Generators;

use Handlebars\Handlebars;

class SubdomainHtaccess implements TemplateProviderInterface
{

    protected $template = <<< HTACCESS
#WPBrowser
#==========================================================
# WPBrowser start - subdomain
#==========================================================
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^wp-admin$ wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^(wp-(content|admin|includes).*) {{subfolder}}$1 [L]
RewriteRule ^(.*\.php)$ $1 [L]
RewriteRule . index.php [L]
#==========================================================
# WPBrowser end
#==========================================================
HTACCESS;

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
