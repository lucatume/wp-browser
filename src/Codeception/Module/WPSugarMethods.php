<?php

namespace Codeception\Module;


trait WPSugarMethods
{

    public function setPermalinkStructureAndFlush($permalinkStructure = '/%postname%/', $hardFlush = true)
    {
        update_option('permalink_structure', $permalinkStructure);
        codecept_debug("Updated permalink structure to '$permalinkStructure'.");
        flush_rewrite_rules($hardFlush);
        codecept_debug('Flushed rewrite rules.');
    }


}