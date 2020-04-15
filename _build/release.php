#!/usr/bin/env php
<?php

namespace tad\WPBrowser;

use tad\WPBrowser\Utils\Map;

$root = dirname( __DIR__ );


require_once $root . '/vendor/autoload.php';

$changelogFile = $root . '/CHANGELOG.md';
$options = getopt('q',['not-interactive'],$optind);
$releaseType   = isset( $argv[$optind] ) ? $argv[$optind] : 'patch';
$notInteractive = isset( $options['q'] ) || isset( $options['not-interactive'] );

if ( ! in_array( $releaseType, [ 'major', 'minor', 'patch' ], true ) ) {
	echo "\e[31mThe release type has to be one of major, minor or patch.\e[0m\n";
	exit( 1 );
}

$gitDirty = trim(shell_exec('git diff HEAD'));
if(!empty($gitDirty)){
	echo "\e[31mYou have uncommited work.\e[0m\n";
	exit( 1 );
}

$gitDiff = trim(shell_exec('git log origin/master..HEAD'));
if(!empty($gitDiff)){
	echo "\e[31mYou have unpushed changes.\e[0m\n";
	exit( 1 );
}

$currentGitBranch = trim( shell_exec( 'git rev-parse --abbrev-ref HEAD' ) );
if ( $currentGitBranch !== 'master' ) {
	echo "\e[31mCan release only from master branch.\e[0m\n";
	exit( 1 );
}
echo "Current git branch: \e[32m" . $currentGitBranch . "\e[0m\n";

/**
 * Parses the changelog to get the latest notes and the latest, released, version.
 *
 * @param string $changelog The absolute path to the changelog file.
 *
 * @return Map The map of parsed values.
 */
function changelog( $changelog ) {
	$notes = '';

	$f    = fopen( $changelog, 'rb' );
	$read = false;
	while ( $line = fgets( $f ) ) {
		if ( preg_match( '/^## \\[unreleased]/', $line ) ) {
			$read = true;
			continue;
		}

		if ( preg_match( '/^## \\[(?<version>\\d+\\.\\d\.\\d+)]/', $line, $m ) ) {
			$latestVersion = $m['version'];
			break;
		}

		if ( $read === true ) {
			$notes .= $line;
		}
	}

	fclose( $f );

	return new Map( [ 'notes' => trim( $notes ), 'latestVersion' => $latestVersion ] );
}

$changelog = changelog( $changelogFile );

switch ( $releaseType ) {
	case 'major':
		$releaseVersion = preg_replace_callback( '/(?<target>\\d+)\\.\\d\.\\d+/', static function ( $m ) {
			return ( ++ $m['target'] ) . '.0.0';
		}, $changelog( 'latestVersion' ) );
		break;
	case 'minor':
		$releaseVersion = preg_replace_callback( '/(?<major>\\d+)\\.(?<target>\\d)\.\\d+/', static function ( $m ) {
			return $m['major'] . '.' . ( ++ $m['target'] ) . '.0';
		}, $changelog( 'latestVersion' ) );
		break;
	case 'patch':
		$releaseVersion = preg_replace_callback( '/(?<major>\\d+)\\.(?<minor>\\d)\.(?<target>\\d+)/',
			static function ( $m ) {
				return $m['major'] . '.' . ( $m['minor'] ) . '.' . ( ++ $m['target'] );
			}, $changelog( 'latestVersion' ) );
		break;
}

$releaseNotesHeader = "## {$releaseVersion}\n\n";
$fullReleaseNotes   = $releaseNotesHeader . $changelog( 'notes' );

echo "Latest release: \e[32m" . $changelog( 'latestVersion' ) . "\e[0m\n";
echo "Release type: \e[32m" . $releaseType . "\e[0m\n";
echo "Next release: \e[32m" . $releaseVersion . "\e[0m\n";
echo "Release notes:\n\n---\n" . $fullReleaseNotes . "\n---\n";
echo "\n\n";

file_put_contents( $root . '/.rel', $fullReleaseNotes );

$releaseCommand = 'git release create -F .rel ' . $releaseVersion;

echo "Releasing with command: \e[32m" . $releaseCommand . "\e[0m\n\n";

if ( $notInteractive || preg_match( '/y/i', readline( 'Do you want to proceed? ' ) ) ) {
	echo "Doing";
//	shell_exec( $releaseCommand );
} else {
	echo "Canceling\n";
}

unlink( $root . '/.rel' );
