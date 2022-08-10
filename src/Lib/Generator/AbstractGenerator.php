<?php

namespace lucatume\WPBrowser\Lib\Generator;

use Codeception\Util\Template;

abstract class AbstractGenerator
{
    /**
     * @var array<string>J
     */
    public static $requiredSettings = [];

    /**
     * @var string
     */
    protected $template = '';

    /**
     * AbstractGenerator constructor.
     *
     * @param array<string,mixed> $settings
     */
    public function __construct(protected array $settings)
    {
        $this->ensureSettings();
        $this->ensureSettingsAreAllStrings();
    }

    /**
     * Checks the template settings.
     *
     * @return bool Whether the template settings are correct or not.
     */
    protected function ensureSettings(): bool
    {
        if (empty(static::$requiredSettings)) {
            return true;
        }

        foreach (static::$requiredSettings as $requiredSetting) {
            if (!isset($this->settings[$requiredSetting])) {
                throw new \BadMethodCallException('Required template setting [{' . $requiredSetting . '}] is missing.');
            }
        }

        return true;
    }

    /**
     * Checks all settings are strings.
     */
    protected function ensureSettingsAreAllStrings(): bool
    {
        if (empty(static::$requiredSettings)) {
            return true;
        }

        foreach (static::$requiredSettings as $requiredSetting) {
            if (!is_string($this->settings[$requiredSetting])) {
                $message = 'Required template setting [{' . $requiredSetting . '}] is not a string.';
                throw new \BadMethodCallException($message);
            }
        }

        return true;
    }

    /**
     * Renders and returns the template..
     *
     * @return string The rendered template.
     */
    public function produce()
    {
        $template = new Template($this->template);
        foreach ($this->settings as $key => $value) {
            $template->place($key, $value);
        }
        return $template->produce();
    }
}
