<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

trait InstalledTrait
{
    public function updateOption(string $option, mixed $value): int
    {
        $db = $this->getDb();
        $options = $this->db->getTablePrefix() . 'options';
        return $db->query("UPDATE $options SET option_value = ':value' WHERE option_name = ':name'", [
            'value' => $value,
            'name' => $option,
        ]);
    }
}
