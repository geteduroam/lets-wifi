<?php declare(strict_types=1);

/**
 * Windows Config profile generator
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator\Windows;

use Uninett\LetsWifi\Generator\EapConfig\EapConfigGenerator;

class WindowsConfigGenerator extends EapConfigGenerator
{
	/** @var ?string */
	private $payload;

	public function __toString(): string
	{
		if ( null === $this->payload ) {
			$workdir = $this->buildWorkspace();
			$this->package( $workdir );
			$this->payload = ''
				. \file_get_contents( __DIR__ . \DIRECTORY_SEPARATOR . '7zS2.sfx' )
				. ';!@Install@!UTF-8!' . "\n"
				. 'Title="eduroam installer"' . "\n"
				. 'GUIMode="1"' . "\n"
				. 'RunProgram="EduroamApp.exe"' . "\n"
				. ';!@InstallEnd@!' . "\n"
				. \file_get_contents( $workdir . \DIRECTORY_SEPARATOR . 'profile.7z' )
				;
			//$this->cleanup( $workdir );
		}

		return $this->payload;
	}

	/** {@inheritdoc} */
	public function getContentType(): string
	{
		return 'application/vnd.microsoft.portable-executable';
	}

	private function buildWorkspace(): string
	{
		$tempfile = \tempnam( \sys_get_temp_dir(), '' );
		if ( false === $tempfile || !\file_exists( $tempfile ) || !\unlink( $tempfile ) ) {
			throw new \RuntimeException( 'Unable to create temporary file' );
		}
		$workdir = $tempfile . \DIRECTORY_SEPARATOR . 'work';
		if ( !\mkdir( $tempfile, 0700 ) || !\mkdir( $workdir, 0700 ) ) {
			throw new \RuntimeException( 'Unable to create temporary directory' );
		}
		if ( !\copy( __DIR__ . \DIRECTORY_SEPARATOR . 'EduroamApp.exe', $workdir . \DIRECTORY_SEPARATOR . 'EduroamApp.exe' ) ) {
			throw new \RuntimeException( 'Unable to link exe file' );
		}
		if ( !\file_put_contents( $workdir . \DIRECTORY_SEPARATOR . 'profile.eap-config', parent::__toString() ) ) {
			throw new \RuntimeException( 'Unable to write profile in working directory' );
		}

		return $tempfile;
	}

	private function package( string $workspace ): void
	{
		$workdir = $workspace . \DIRECTORY_SEPARATOR . 'work' . \DIRECTORY_SEPARATOR;
		$outputFile = $workspace . \DIRECTORY_SEPARATOR . 'profile.7z';
		$cmd = "7z a ${outputFile} ${workdir}EduroamApp.exe ${workdir}profile.eap-config";
		\exec( $cmd, $output, $result );
		if ( $result ) {
			throw new \RuntimeException( $cmd . "\r\n" . \implode( "\r\n", $output ) );
		}
	}
}
