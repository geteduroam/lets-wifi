<?php declare(strict_types=1);

/**
 * Wrapper class around IAuthenticationMethod for keeping constant UUIDs
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator\Apple;

use Uninett\LetsWifi\UUID;
use Uninett\LetsWifi\Authentication\IAuthenticationMethod;

class AppleMobileConfigWifiAuthentication
{
	/** @var IAuthenticationMethod */
	private $authentication;

	/** @var null|UUID */
	private $caUuid;

	/** @var null|UUID */
	private $certUuid;

	/** @var null|UUID */
	private $wifiUuid;

	/**
	 * Construct a new authentication wrapper
	 *
	 * @param IAuthenticationMethod $authentication The authentication object to wrap around
	 */
	public function __construct( IAuthenticationMethod $authentication )
	{
		$this->authentication = $authentication;
	}

	/**
	 * Get the wrapped around authentication object
	 *
	 * @return IAuthenticationMethod
	 */
	public function getAuthentication(): IAuthenticationMethod
	{
		return $this->authentication;
	}

	/**
	 * Get a unique UUID for the CA
	 *
	 * @return UUID
	 */
	public function getCaUuid(): UUID
	{
		if ( null === $this->caUuid ) {
			$this->caUuid = new UUID();
		}

		return $this->caUuid;
	}

	/**
	 * Get a unique UUID for the user certificate
	 *
	 * @return UUID
	 */
	public function getCertUuid(): UUID
	{
		if ( null === $this->certUuid ) {
			$this->certUuid = new UUID();
		}

		return $this->certUuid;
	}

	/**
	 * Get a uniquie UUID for the wifi configuration
	 *
	 * @return UUID
	 */
	public function getWifiUuid(): UUID
	{
		if ( null === $this->wifiUuid ) {
			$this->wifiUuid = new UUID();
		}

		return $this->wifiUuid;
	}
}
