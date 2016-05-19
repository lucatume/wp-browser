<?php

function unlinkDir($dir)
{
    $dirs = array($dir);
    $files = array();
    for ($i = 0; ; $i++) {
        if (isset($dirs[$i]))
            $dir = $dirs[$i];
        else
            break;

        if ($openDir = opendir($dir)) {
            while ($readDir = @readdir($openDir)) {
                if ($readDir != "." && $readDir != "..") {

                    if (is_dir($dir . "/" . $readDir)) {
                        $dirs[] = $dir . "/" . $readDir;
                    } else {

                        $files[] = $dir . "/" . $readDir;
                    }
                }
            }

        }

    }


    foreach ($files as $file) {
        unlink($file);

    }
    $dirs = array_reverse($dirs);
    foreach ($dirs as $dir) {
        rmdir($dir);
    }

}
