<?php

namespace tad\WPBrowser\Generators;

use Handlebars\Handlebars;

class SubfolderHtaccess implements TemplateProviderInterface
{

    protected $templates = <<< HTACCESS
#WPBrowser
#==========================================================
# WPBrowser start - subfolder
#==========================================================
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) {{subfolder}}$2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
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
