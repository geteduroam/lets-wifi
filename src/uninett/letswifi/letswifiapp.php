<?php declare(strict_types=1);

/**
 * Let's Wifi App
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi;

use DateInterval;

use ParagonIE\Paseto\Keys\SymmetricKey;

use League\OAuth2\Client\Provider\GenericProvider;

final class LetsWifiApp
{
	/** @var ?self */
	private static $instance;

	/** @var array */
	private $config;

	public function __construct( array $config = null )
	{
		if ( null === $config ) {
			/** @psalm-suppress UnresolvableInclude */
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
		return \array_key_exists( 'authPrincipal', $this->config )
			? $this->config['authPrincipal']
			: 'lets-wifi-auth'
			;
	}

	public function getIssuerPrincipal(): string
	{
		return \array_key_exists( 'issuerPrincipal', $this->config )
			? $this->config['issuerPrincipal']
			: 'lets-wifi-issuer'
			;
	}

	public function getGeneratorPrincipal(): string
	{
		return \array_key_exists( 'generatorPrincipal', $this->config )
			? $this->config['generatorPrincipal']
			: 'lets-wifi-generator'
			;
	}

	public function getAuthTokenValidity(): DateInterval
	{
		return \array_key_exists( 'authTokenValidity', $this->config )
			? new DateInterval( $this->config['authTokenValidity'] )
			: new DateInterval( 'PT5M' )
			;
	}

	public function getIdTokenValidity(): DateInterval
	{
		return \array_key_exists( 'idTokenValidity', $this->config )
			? new DateInterval( $this->config['idTokenValidity'] )
			: new DateInterval( 'PT15M' )
			;
	}

	public function getServerAdministratorUsers(): array
	{
		return \array_key_exists( 'serverAdministratorUsers', $this->config )
			? $this->config['serverAdministratorUsers']
			: []
			;
	}

	public function getRealm(): string
	{
		return $this->config['realm'];
	}

	public function getCertificateSubjectAttributes(): array
	{
		return $this->config['certificateSubjectAttributes'];
	}

	public function getOAuthProvider(): GenericProvider
	{
		return new GenericProvider( $this->config['oauthProvider'] );
	}
}
