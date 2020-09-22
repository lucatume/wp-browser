<?php
/**
 * Functions dedicated to interaction with Composer.
 *
 * @package lucatume/WPBrowser
 */

namespace lucatume\WPBrowser;

use tad\WPBrowser\Utils\Map;

/**
 * Parses a Composer configuration file into a map.
 *
 * @param string|false|null $file The file to read.
 *
 * @return Map The parsed file map.
 */
function composerFile($file)
{
    if (empty($file) || ! is_file($file)) {
        throw new \InvalidArgumentException("File {$file} does not exist.");
    }
    $composerFileContents = file_get_contents($file);

    if ($composerFileContents === false) {
        throw new \InvalidArgumentException("Cannot read file {$file}.");
    }

    $decoded = json_decode($composerFileContents, true);

    if ($decoded === null) {
        throw new \InvalidArgumentException("Cannot parse the contents of the {$file} file.");
    }

    return new Map($decoded);
}


/**
 * Checks the currently defined Composer dependencies to make sure required packages are present.
 *
 * @param Map                  $composerFile The map of a loaded composer.json file contents.
 *
 * @param array<string,string> $dependencies A map of dependencies and their required versions.
 * @param callable             $else         Teh callback that will be called if the dependency check fails.
 *
 * @return void
 */
function checkComposerDependencies(Map $composerFile, array $dependencies, callable $else)
{
    $require     = $composerFile('require', []);
    $requireDev  = $composerFile('require-dev', []);
    $allRequired = array_merge($require, $requireDev);
    $found       = array_intersect_key($dependencies, $allRequired);

    if (count($found) !== count($dependencies)) {
        $lines = [];
        foreach ($dependencies as $package => $version) {
            $lines[] = sprintf('"%s": "%s"', $package, $version);
        }

        $else($lines);
    }
}
