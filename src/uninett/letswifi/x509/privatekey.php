<?php declare(strict_types=1);

/**
 * Private key
 *
 * @see http://php.net/manual/en/function.openssl-pkey-new.php
 * @see http://php.net/manual/en/function.openssl-pkey-get-private.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

class PrivateKey extends AbstractKeyResource implements IPrivateKey
{
	/**
	 * Generate a new private key
	 *
	 * @see http://php.net/manual/en/function.openssl-pkey-new.php
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @param IKeyConfig $configargs Configuration arguments like in \openssl_csr_new()
	 *
	 * @throws OpenSSLException
	 *
	 * @return \Uninett\LetsWifi\X509\PrivateKey
	 */
	public static function generate( IKeyConfig $configargs ): self
	{
		OpenSSLException::flushErrorMessages();
		$key = \openssl_pkey_new( $configargs->toArray() );
		if ( false === $key ) {
			throw new OpenSSLException();
		}

		return new self( $key );
	}

	/**
	 * Import an existing private key
	 *
	 * @see http://php.net/manual/en/function.openssl-pkey-get-private.php
	 *
	 * @param mixed  $keyMaterial An existing resource, or a PEM formatted key
	 * @param string $passphrase  Passphrase for the key, if any
	 *
	 * @throws OpenSSLException
	 *
	 * @return \Uninett\LetsWifi\X509\PrivateKey
	 */
	public static function import( $keyMaterial, string $passphrase = '' ): self
	{
		OpenSSLException::flushErrorMessages();
		$res = \openssl_pkey_get_private( $keyMaterial, $passphrase );
		if ( false === $res ) {
			throw new OpenSSLException();
		}

		return new self( $res );
	}

	/** {@inheritdoc} */
	public function exportPEMWithoutPassword(): string
	{
		$out = '';
		OpenSSLException::flushErrorMessages();
		if ( !\openssl_pkey_export( $this->getResource(), $out, '' ) ) {
			throw new OpenSSLException();
		}

		return $out;
	}

	/** {@inheritdoc} */
	public function exportPEM( string $passphrase ): string
	{
		if ( 0 === \strlen( $passphrase ) ) {
			throw new \InvalidArgumentException( 'Passphrase cannot be empty' );
		}

		$out = '';
		OpenSSLException::flushErrorMessages();
		if ( \openssl_pkey_export( $this->getResource(), $out, $passphrase ) ) {
			throw new OpenSSLException();
		}

		return $out;
	}
}
