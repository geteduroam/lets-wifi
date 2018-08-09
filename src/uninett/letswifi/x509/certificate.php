<?php declare(strict_types=1);

/**
 * Certificate
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

class Certificate extends PublicKey implements ICertificate
{
	/** @var ?IPrivateKey */
	private $privateKey;

	/** @var ICertificate[] */
	private $chain;

	/**
	 * @param mixed          $key        An existing resource, or a PEM formatted key
	 * @param IPrivateKey    $privateKey The private key for this certificate
	 * @param ICertificate[] $chain      Chain for this certificate
	 */
	public function __construct( $key, ?IPrivateKey $privateKey = null, ?array $chain = [] )
	{
		parent::__construct( self::keyToResource( $key ) );

		if ( null === $chain ) {
			$chain = [];
		}

		$this->privateKey = $privateKey;
		$this->chain = $chain;
	}

	/** {@inheritdoc} */
	public function exportPKCS12WithPrivateKey( string $password ): IPKCS12
	{
		$privKey = $this->getPrivateKey();
		if ( null === $privKey ) {
			throw new \RuntimeException( 'Can only export PKCS12 with private key if a private key is set' );
		}

		$args = [];
		$chain = $this->getChain();
		if ( sizeof( $chain ) > 0 ) {
			$args['extracerts'] = [];
			foreach ( $chain as $cert ) {
				$args['extracerts'][] = $cert->getResource();
			}
		}

		$out = '';
		OpenSSLException::flushErrorMessages();
		if ( !\openssl_pkcs12_export( $this->getResource(), $out, $privKey->getResource(), $password, $args ) ) {
			throw new OpenSSLException();
		}

		return new PKCS12( $out );
	}

	/** {@inheritdoc} */
	public function getChain(): array
	{
		return $this->chain;
	}

	/** {@inheritdoc} */
	public function getPrivateKey(): ?IPrivateKey
	{
		return $this->privateKey;
	}

	/** {@inheritdoc} */
	public function getPEMBytes( bool $text = false ): string
	{
		$output = '';
		OpenSSLException::flushErrorMessages();
		if ( !\openssl_x509_export( $this->getResource(), $output, !$text ) ) {
			throw new OpenSSLException();
		}

		return $output;
	}

	/**
	 * Convert key material to a native PHP resource
	 *
	 * @see http://php.net/manual/en/function.openssl-x509-read.php
	 *
	 * @param mixed $keyMaterial
	 *
	 * @throws OpenSSLException
	 *
	 * @return resource
	 */
	protected static function keyToResource( $keyMaterial )
	{
		OpenSSLException::flushErrorMessages();
		$res = \openssl_x509_read( $keyMaterial );
		if ( false === $res ) {
			throw new OpenSSLException();
		}

		return $res;
	}
}
