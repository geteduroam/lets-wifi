<?php declare(strict_types=1);

/**
 * Class that treats an OpenSSL resource
 * that can be read with openssl_pkey_get_details()
 *
 * @see http://php.net/manual/en/function.openssl-pkey-get-details.php
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

abstract class AbstractKeyResource implements IKey
{
	/** @var resource */
	private $res;

	/** @var ?array */
	private $details;

	/**
	 * Create a new key object, either from existing key data, or by generating a new key.
	 *
	 * @param ?resource $res Private key
	 */
	public function __construct( $res )
	{
		if ( \is_resource( $res ) ) {
			$this->res = $res;
		} else {
			throw new \InvalidArgumentException( '$res must be resource' );
		}
	}

	/** {@inheritdoc} */
	public function getBits(): int
	{
		return $this->getDetails()['bits'];
	}

	/** {@inheritdoc} */
	public function getType(): EOpensslKeyType
	{
		return new EOpensslKeyType( $this->getDetails()['type'] );
	}

	/** {@inheritdoc} */
	public function getPublicKey(): IPublicKey
	{
		OpenSSLException::flushErrorMessages();
		$res = \openssl_pkey_get_public( $this->getResource() );
		if ( false === $res ) {
			throw new OpenSSLException();
		}

		return new PublicKey( $res );
	}

	/** {@inheritdoc} */
	public function getResource()
	{
		return $this->res;
	}

	/**
	 * Get details about this key
	 *
	 * @see http://php.net/manual/en/function.openssl-pkey-get-details.php
	 *
	 * @throws OpenSSLException
	 */
	private function getDetails(): array
	{
		if ( null === $this->details ) {
			OpenSSLException::flushErrorMessages();
			$details = \openssl_pkey_get_details( $this->getResource() );
			if ( false === $details ) {
				throw new OpenSSLException();
			}
			$this->details = $details;
		}

		return $this->details;
	}
}
