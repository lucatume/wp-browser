<?php
/**
 * Stub files and classes required to satisfy phpstan checks.
 */

// Alias for Symfony < 4.3
if (!class_exists('Symfony\Component\BrowserKit\AbstractBrowser') && class_exists('Symfony\Component\BrowserKit\Client')) {
	class_alias('Symfony\Component\BrowserKit\Client', 'Symfony\Component\BrowserKit\AbstractBrowser');
}
