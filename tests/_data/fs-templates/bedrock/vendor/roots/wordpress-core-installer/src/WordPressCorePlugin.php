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

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class WordPressCorePlugin implements PluginInterface {

	/**
	 * Apply plugin modifications to composer
	 *
	 * @param Composer    $composer
	 * @param IOInterface $io
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$installer = new WordPressCoreInstaller( $io, $composer );
		$composer->getInstallationManager()->addInstaller( $installer );
	}

	/**
	 * {@inheritDoc}
	 */
	public function deactivate( Composer $composer, IOInterface $io ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function uninstall( Composer $composer, IOInterface $io ) {
	}

}
