<?php

namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

abstract class AbstractGenerator
{
    public static $requiredSettings = [];
    protected $template = '';
    protected $settings = [];

    /**
     * AbstractGenerator constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->ensureSettings();
        $this->ensureSettingsAreAllStrings();
    }

    protected function ensureSettings()
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

    protected function ensureSettingsAreAllStrings()
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

    public function produce()
    {
        $template = new Template($this->template);
        foreach ($this->settings as $key => $value) {
            $template->place($key, $value);
        }
        return $template->produce();
    }
}
