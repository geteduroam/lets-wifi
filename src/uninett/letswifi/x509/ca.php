<?php declare(strict_types=1);

/**
 * CSR - Certificate Signing Request
 *
 * @see http://php.net/manual/en/function.openssl-csr-sign.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

class CA extends Certificate
{
	/** @var string */
	private $workingDirectory;

	/** @var string */
	private $pubKeyFileName;

	/** @var string */
	private $privKeyFileName;

	/**
	 * Load an existing CA
	 *
	 * @param string $workingDirectory Directory where the CA, key, index and serial reside
	 * @param string $pubKeyFileName   Filename of the public key/certificate, must be in $workingDirectory
	 * @param string $privKeyFileName  Filename of the private key, must be in $workingDirectory
	 * @param string $caPassPhrase     Passphrase on the private key
	 *
	 * @throws \RuntimeException $workingDirectory cannot be found, or cannot read one or more of the expected files there
	 */
	public function __construct( string $workingDirectory, string $pubKeyFileName, string $privKeyFileName, string $caPassPhrase )
	{
		$realWorkingDirectory = \realpath( $workingDirectory );
		if ( false === $realWorkingDirectory ) {
			throw new \RuntimeException( "Unable to resolve working directory: ${workingDirectory}" );
		}
		$this->workingDirectory = $realWorkingDirectory;
		$this->pubKeyFileName = $pubKeyFileName;
		$this->privKeyFileName = $privKeyFileName;

		parent::__construct(
				Certificate::keyToResource( 'file://' . $realWorkingDirectory . \DIRECTORY_SEPARATOR . $pubKeyFileName ),
				PrivateKey::import( 'file://' . $realWorkingDirectory . \DIRECTORY_SEPARATOR . $privKeyFileName, $caPassPhrase )
			);
	}

	/**
	 * Sign a CSR
	 *
	 * @see ICSR
	 *
	 * @param ICSR        $csr        CSR to sign
	 * @param int         $days       Validity of signed certificate in days, counting from this function call
	 * @param ?IKeyConfig $configargs Optional configuration to influence the signing process
	 * @param int         $serial     Serial number of the certificate
	 *
	 * @throws \RuntimeException Some required CA information was not available
	 * @throws OpenSSLException  An internal PHP error occurred
	 *
	 * @return ICertificate
	 */
	public function sign( ICSR $csr, int $days, ?IKeyConfig $configArgs = null, int $serial = 0 ): ICertificate
	{
		$private = $this->getPrivateKey();
		if ( null === $private ) {
			throw new \RuntimeException( 'The private key for this CA is unknown, signing not possible' );
		}

		if ( null === $configArgs ) {
			$configArgs = new KeyConfig();
		}

		$cwd = \getcwd();
		if ( !\chdir( $this->workingDirectory ) ) {
			throw new \RuntimeException( 'Unable to chdir to CA working directory' );
		}

		OpenSSLException::flushErrorMessages();
		$result = \openssl_csr_sign(
				$csr->getResource(), /* CSR */
				$this->getResource(), /* CA certificate */
				$private->getResource(), /* CA privkey */
				$days, /* Days validity */
				$configArgs->toArray(),
				$serial
			);

		if ( false === $result ) {
			throw new OpenSSLException();
		}
		if ( !\chdir( $cwd ) ) {
			throw new \RuntimeException( 'Unable to chdir back from CA working directory' );
		}

		return new Certificate( $result, $csr->getPrivateKey(), \array_merge( [$this], $this->getChain() ) );
	}

	public static function selfSign( ICSR $csr, int $days, ?IKeyConfig $configArgs = null, int $serial = 0 ): ICertificate
	{
		$private = $csr->getPrivateKey();

		if ( null === $configArgs ) {
			$configArgs = new KeyConfig();
		}

		OpenSSLException::flushErrorMessages();
		/**
		 * Using NULL for the CA certificate, this is valid according to PHP documentation,
		 * but not according to Psalm.  Suppressing the error.
		 *
		 * @see http://php.net/openssl_csr_sign
		 * @psalm-suppress NullArgument
		 */

		$result = \openssl_csr_sign(
				$csr->getResource(), /* CSR */
				null, /* CA certificate */
				$private->getResource(), /* CA privkey */
				$days, /* Days validity */
				$configArgs->toArray(),
				$serial
			);

		if ( false === $result ) {
			throw new OpenSSLException();
		}

		return new Certificate( $result, $csr->getPrivateKey() );
	}
}
