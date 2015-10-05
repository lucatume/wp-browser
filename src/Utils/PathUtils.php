<?php
namespace tad\WPBrowser\Utils;

class PathUtils
{

    /**
     * Replaces the `~` symbol with the user home path.
     *
     * @param string $path
     * @return string The path with the `~` replaced with the user home path if any.
     */
    public static function homeify($path, SystemLocals $locals = null)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Paht must be a string');
        }
        if (empty($locals)) {
            $locals = new SystemLocals();
        }
        $userHome = $locals->home();
        if (!(empty($userHome) && false !== strpos($path, '~'))) {
            $path = str_replace('~', $userHome, $path);
        }
        return $path;
    }
}