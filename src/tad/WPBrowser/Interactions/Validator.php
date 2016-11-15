<?php

namespace tad\WPBrowser\Interactions;

class Validator
{

	public function noSpaces($message)
	{
		return function ($answer) use ($message) {
			$answer = trim($answer);
			if (false !== strpos($answer, ' ')) {
				throw new \RuntimeException($message);
			}
			return trim($answer);
		};
	}

	public function isUrl($message)
	{
		return function ($answer) use ($message) {
			$answer = trim($answer);
			if (!(filter_var($answer, FILTER_VALIDATE_URL))) {
				throw new \RuntimeException($message);
			}
			return trim($answer);
		};
	}

	public function isWpDir()
	{
		return function ($answer) {
			if (!(is_dir($answer) && file_exists(rtrim($answer, '/') . '/wp-load.php'))) {
				throw new \RuntimeException(
					"'$answer' is not a WP root folder, does not exist or is not accessible; the wp root folder is the one that contains the 'wp-load.php' file"
				);
			}
			return rtrim(trim($answer), '/');
		};
	}

	public function isEmail()
	{
		return function ($answer) {
			if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
				throw new \RuntimeException("'$answer' is not a valid email address");
			}
			return trim($answer);
		};
	}

	public function isRelativeWpAdminDir($root)
	{
		return function ($answer) use ($root) {
			$answer = trim($answer, '/');
			$path = $root . '/' . $answer;
			if (!is_dir($path)) {
				throw new \RuntimeException("'$path' does not exist or is not accessible");
			}
			return '/' . $answer;
		};
	}

	public function isPlugin()
	{
		return function ($answer) {
			$answer = trim($answer);
			if (empty($answer)) {
				return $answer;
			}
			if (empty($answer) || !preg_match('~^[\\w-]+(/[\\w-]+)?\\.php$~ui', $answer)) {
				throw new \RuntimeException(
					"'$answer' is not in the 'hello.php' or 'acme/plugin.php' format, leave blank to move on"
				);
			}
			return $answer;
		};
	}

	public function isTheme()
	{
		return function ($answer) {
			if (empty($answer)) {
				throw new \RuntimeException("Theme cannot be empty");
			}

			$answer = trim(trim($answer), ',');

			if (!preg_match('/^[,A-Za-z0-9-_]+$/', $answer)) {
				throw new \RuntimeException("{$answer} contains invalid chars");
			}

			return strpos($answer, ',') !== false ? preg_split('/\\s*,\\s*/', $answer) : [$answer];
		};
	}
}