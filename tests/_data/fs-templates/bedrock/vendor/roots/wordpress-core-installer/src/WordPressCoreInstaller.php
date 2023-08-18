<?php

/**
 * WordPress Core Installer - A Composer to install WordPress in a webroot subdirectory
 * Copyright (C) 2013    John P. Bloch
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Roots\Composer;

use Composer\Config;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class WordPressCoreInstaller extends LibraryInstaller {

	const TYPE = 'wordpress-core';

	const MESSAGE_CONFLICT = 'Two packages (%s and %s) cannot share the same directory!';
	const MESSAGE_SENSITIVE = 'Warning! %s is an invalid WordPress install directory (from %s)!';

	private static $_installedPaths = array();

	private $sensitiveDirectories = array( '.' );

	/**
	 * {@inheritDoc}
	 */
	public function getInstallPath( PackageInterface $package ) {
		$installationDir = false;
		$prettyName      = $package->getPrettyName();
		if ( $this->composer->getPackage() ) {
			$topExtra = $this->composer->getPackage()->getExtra();
			if ( ! empty( $topExtra['wordpress-install-dir'] ) ) {
				$installationDir = $topExtra['wordpress-install-dir'];
				if ( is_array( $installationDir ) ) {
					$installationDir = empty( $installationDir[ $prettyName ] ) ? false : $installationDir[ $prettyName ];
				}
			}
		}
		$extra = $package->getExtra();
		if ( ! $installationDir && ! empty( $extra['wordpress-install-dir'] ) ) {
			$installationDir = $extra['wordpress-install-dir'];
		}
		if ( ! $installationDir ) {
			$installationDir = 'wordpress';
		}
		$vendorDir = $this->composer->getConfig()->get( 'vendor-dir', Config::RELATIVE_PATHS ) ?: 'vendor';
		if (
			in_array( $installationDir, $this->sensitiveDirectories ) ||
			( $installationDir === $vendorDir )
		) {
			throw new \InvalidArgumentException( $this->getSensitiveDirectoryMessage( $installationDir, $prettyName ) );
		}
		if (
			! empty( self::$_installedPaths[ $installationDir ] ) &&
			$prettyName !== self::$_installedPaths[ $installationDir ] &&
			$package->getType() !== self::TYPE
		) {
			$conflict_message = $this->getConflictMessage( $prettyName, self::$_installedPaths[ $installationDir ] );
			throw new \InvalidArgumentException( $conflict_message );
		}
		self::$_installedPaths[ $installationDir ] = $prettyName;

		return $installationDir;
	}

	/**
	 * {@inheritDoc}
	 */
	public function supports( $packageType ) {
		return self::TYPE === $packageType;
	}

	/**
	 * Get the exception message with conflicting packages
	 *
	 * @param string $attempted
	 * @param string $alreadyExists
	 *
	 * @return string
	 */
	private function getConflictMessage( $attempted, $alreadyExists ) {
		return sprintf( self::MESSAGE_CONFLICT, $attempted, $alreadyExists );
	}

	/**
	 * Get the exception message for attempted sensitive directories
	 *
	 * @param string $attempted
	 * @param string $packageName
	 *
	 * @return string
	 */
	private function getSensitiveDirectoryMessage( $attempted, $packageName ) {
		return sprintf( self::MESSAGE_SENSITIVE, $attempted, $packageName );
	}

}
