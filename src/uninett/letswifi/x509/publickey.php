<?php declare(strict_types=1);

/**
 * Public key
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

class PublicKey extends AbstractKeyResource implements IPublicKey
{
	/**
	 * Import an existing public key
	 *
	 * @see http://php.net/manual/en/function.openssl-pkey-get-private.php
	 *
	 * @param mixed $keyMaterial An existing resource, or a PEM formatted key
	 *
	 * @throws OpenSSLException
	 *
	 * @return \Uninett\LetsWifi\X509\PublicKey
	 */
	public static function importPublicKey( $keyMaterial ): self
	{
		OpenSSLException::flushErrorMessages();
		$res = \openssl_pkey_get_public( $keyMaterial );
		if ( false === $res ) {
			throw new OpenSSLException();
		}

		return new self( $res );
	}
}
