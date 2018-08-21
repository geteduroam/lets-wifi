<?php declare(strict_types=1);

/**
 * Let's Wifi App
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi;

use ParagonIE\Paseto\Keys\SymmetricKey;

use League\OAuth2\Client\Provider\GenericProvider;

Use DateInterval;

class LetsWifiApp
{
	/** @var self */
	private static $instance;

	public function __construct( array $config = null )
	{
		if ( null === $config ) {
			$config = ( require \implode( \DIRECTORY_SEPARATOR, [\dirname( __DIR__, 3 ), 'etc', 'lets-wifi.php'] ) );
		}
		$this->config = $config;
	}

	public static function getInstance(): self
	{
		if ( null === static::$instance ) {
			static::$instance = new static( null );
		}

		return static::$instance;
	}

	public function getSymmetricKey(): SymmetricKey
	{
		return new SymmetricKey( $this->config['symmetricKey'] );
	}

	public function getClients(): array
	{
		return $this->config['clients'];
	}

	public function getAuthPrincipal(): string
	{
		return $this->config['authPrincipal'];
	}

	public function getIssuerPrincipal(): string
	{
		return $this->config['issuerPrincipal'];
	}

	public function getGeneratorPrincipal(): string
	{
		return $this->config['generalPrincipal'];
	}

	public function getAuthTokenValidity(): DateInterval
	{
		return new DateInterval( $this->config['authTokenValidity'] );
	}

	public function getIdTokenValidity(): DateInterval
	{
		return new DateInterval( $this->config['idTokenValidity'] );
	}

	public function getServerAdministratorUsers(): array
	{
		return $this->config['serverAdministratorUsers'];
	}

	public function getRealm(): string
	{
		return $this->config['realm'];
	}

	public function getCertificateSubjectAttributes(): array
	{
		return $this->config['certificateSubjectAttributes'];
	}

	public function getOAuthProvider() {
		return new GenericProvider( $this->config['oauthProvider'] );
	}
}
