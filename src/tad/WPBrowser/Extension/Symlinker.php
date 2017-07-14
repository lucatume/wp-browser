<?php

namespace tad\WPBrowser\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Filesystem\Exception\IOException;
use tad\WPBrowser\Filesystem\Filesystem;

class Symlinker extends Extension
{
	public static $events = [
		Events::MODULE_INIT => 'symlink',
		Events::SUITE_AFTER => 'unlink',
	];

	/**
	 * @var array
	 */
	protected $required = ['mode' => ['plugin', 'theme'], 'destination'];

	/**
	 * @var string
	 */
	protected $destination;

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	public function __construct($config, $options, Filesystem $filesystem = null)
	{
		$this->filesystem = $filesystem ? $filesystem : new Filesystem();
		parent::__construct($config, $options);
	}

	public function symlink(\Codeception\Event\SuiteEvent $e)
	{
		$rootFolder = $this->getRootFolder($e->getSettings());
		$destination = $this->getDestination($rootFolder, $e->getSettings());

		try {
			if (!$this->filesystem->file_exists($destination)) {
				$this->filesystem->symlink($rootFolder, $destination, true);
				$this->writeln('Symbolically linked plugin folder [' . $destination . ']');
			}
		} catch (IOException $e) {
			throw new ExtensionException(__CLASS__,
				"Error while trying to symlink plugin or theme to destination.\n\n" . $e->getMessage());
		}
	}

	/**
	 * @return string
	 */
	protected function getRootFolder(array $settings = null)
	{
		$rootFolder = isset($this->config['rootFolder']) ? $this->config['rootFolder'] : rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR);

		if (is_array($rootFolder)) {
			$currentEnvs = $this->getCurrentEnvsFromSettings($settings);
			$fallbackRootFolder = isset($rootFolder['default']) ? $rootFolder['default'] : reset($rootFolder);
			$supportedEnvs      = array_intersect(array_keys($rootFolder), $currentEnvs);
			$firstSupported     = reset($supportedEnvs);
			$rootFolder         = isset($rootFolder[$firstSupported]) ? $rootFolder[$firstSupported] : $fallbackRootFolder;
		}

		return $rootFolder;
	}

	/**
	 * @param $rootFolder
	 * @param array $settings
	 * @return array|mixed|string
	 */
	protected function getDestination($rootFolder, array $settings = null)
	{
		$destination = $this->config['destination'];

		if (is_array($destination)) {
			$currentEnvs = $this->getCurrentEnvsFromSettings($settings);
			$fallbackDestination = isset($destination['default']) ? $destination['default'] : reset($destination);
			$supportedEnvs = array_intersect(array_keys($destination), $currentEnvs);
			$firstSupported = reset($supportedEnvs);
			$destination = isset($destination[$firstSupported]) ? $destination[$firstSupported] : $fallbackDestination;
		}
		$destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($rootFolder);

		return $destination;
	}

	public function unlink(\Codeception\Event\SuiteEvent $e)
	{
		$rootFolder = $this->getRootFolder($e->getSettings());
		$destination = $this->getDestination($rootFolder, $e->getSettings());

		if ($this->filesystem->file_exists($destination)) {
			$unlinked = $this->filesystem->unlink($destination);
			if (!$unlinked) {
				// let's not kill the suite but let's notify the user
				$this->writeln('Could not unlink file [' . $destination . '], manual removal is required.');
			}

			$this->writeln('Unliked plugin folder [' . $destination . ']');
		}
	}

	public function _initialize()
	{
		parent::_initialize();
		$this->checkRequirements();
	}

	protected function checkRequirements()
	{
		if (!isset($this->config['mode'])) {
			throw new ExtensionException(__CLASS__, 'Required configuration parameter [mode] is missing.');
		}
		if (!array_intersect($this->required['mode'], (array)$this->config['mode'])) {
			throw new ExtensionException(__CLASS__,
				'[mode] should be one among these values: [' . implode(', ', $this->required['mode']) . ']');
		}
		if (!isset($this->config['destination'])) {
			throw new ExtensionException(__CLASS__, 'Required configuration parameter [destination] is missing.');
		}

		$destination = $this->config['destination'];

		if (is_array($destination)) {
			array_walk($destination, [$this, 'checkDestination']);
		} else {
			$this->checkDestination($destination);
		}

		if (isset($this->config['rootFolder'])) {
			$rootFolder = $this->config['rootFolder'];
			if (is_array($rootFolder)) {
				array_walk($rootFolder, [$this, 'checkRootFolder']);
			}
			else {
				$this->checkRootFolder($rootFolder);
			}
		}
	}

	/**
	 * @param $destination
	 * @throws ExtensionException
	 */
	protected function checkDestination($destination)
	{
		if (!($this->filesystem->is_dir($destination) && $this->filesystem->is_writeable($destination))) {
			throw new ExtensionException(__CLASS__,
				'[destination] parameter [' . $destination . '] is not an existing and writeable directory.');
		}
	}

	protected function checkRootFolder($rootFolder)
	{
		if (!($this->filesystem->is_dir($rootFolder) && $this->filesystem->is_readable($rootFolder))) {
			throw new ExtensionException(__CLASS__,
				'[rootFolder] parameter [' . $rootFolder . '] is not an existing and readable directory.');
		}
	}

	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	protected function getCurrentEnvsFromSettings(array $settings) {
		$rawCurrentEnvs = empty($settings['current_environment']) ? 'default' : $settings['current_environment'];

		return preg_split('/\\s*,\\s*/', $rawCurrentEnvs);
	}
}
