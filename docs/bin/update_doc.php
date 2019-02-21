<?php
if ($argc !== 3) {
    echo 'Usage: "echo ${source_file} ${dest_file} | xargs php udpate_doc.php"';
    exit(1);
}

$src = $argv[1];
$dest = $argv[2];

if (!file_exists($src)) {
    echo "Source file {$src} does not exist!";
    exit(1);
}

if (!file_exists($dest)) {
    echo "Destination file {$dest} does not exist!";
    exit(1);
}

printf('Source file: %s', realpath($src));
printf(PHP_EOL . 'Destination file: %s', realpath($dest));

$src_contents = file_get_contents($src);
$dest_contents = file_get_contents($dest);

$updated_dest_contents = preg_match(
    '/(?<before>.*'
    . preg_quote('<!--doc-->', '/')
    . ').*(?<after>'
    . preg_quote('<!--/doc-->', '/')
    . '.*)/usm',
    $dest_contents,
    $matches
);

if (!isset($matches['before'], $matches['after'])) {
    echo PHP_EOL . "File {$dest} does not contain any <!--doc-->.*<!--/doc--> fenced entry.";
    exit(1);
}

$updated_dest_contents = $matches['before'] . PHP_EOL . $src_contents . $matches['after'];

$updated = file_put_contents($dest, $updated_dest_contents);

if (!$updated) {
    echo PHP_EOL . "File {$dest} could not be updated.";
    exit(1);
}

echo PHP_EOL . "File {$dest} contents updated.";
exit(0);
