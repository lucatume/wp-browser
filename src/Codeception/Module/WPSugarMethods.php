<?php

namespace Codeception\Module;


trait WPSugarMethods
{

	/**
	 * Sets the permalink structure to use and flushes the rewrite rules.
	 * j
	 * @param string $permalinkStructure The new permalink structure; if empty then the `permalink_structure` option will be removed.
	 * @param bool $hardFlush Whether the rewrite rules should be written to the .htaccess file or not.
	 */
	public function setPermalinkStructureAndFlush($permalinkStructure = '/%postname%/', $hardFlush = true)
	{
		if (!empty($permalinkStructure)) {
			update_option('permalink_structure', $permalinkStructure);
			codecept_debug("Updated permalink structure to '$permalinkStructure'.");
		} else {
			delete_option('permalink_structure');
			codecept_debug("Restored permalink structure to WordPress default.");
		}
		flush_rewrite_rules($hardFlush);
		codecept_debug('Flushed rewrite rules.');
	}

	/**
	 * Includes a WordPress component file that's not included by default by WordPress.
	 *
	 * @param string $component The component human-readable name, see the supportedWpComponents() for a list.
	 */
	public function loadWpComponent($component)
	{
		if (!is_string($component)) {
			throw new \InvalidArgumentException('Component name must be a string');
		}

		$supportedComponents = $this->supportedWpComponents();

		if (!isset($supportedComponents[$component])) {
			throw new \InvalidArgumentException("Component '$component' is not supported.");
		}

		$componentPath = $this->config['wpRootFolder'] . DIRECTORY_SEPARATOR . $supportedComponents[$component];
		if (!file_exists($componentPath)) {
			throw new \InvalidArgumentException("Component path [$componentPath] does not exist.");
		}

		include_once($componentPath);
		codecept_debug("Included the [$component] component including file [$componentPath]");
	}

	/**
	 * Returns a list of WordPress components that would not be included by default in a WordPress bootstrap.
	 *
	 * @return array An associative array of human readable name to `wpRootFolder` relative file path.
	 */
	public function supportedWpComponents()
	{
		return ['plugins' => 'wp-admin/includes/plugin.php'];
	}


}