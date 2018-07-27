<?php declare(strict_types=1);

/**
 * EAP-TLS authentication method
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Authentication;

use Uninett\LetsWifi\X509\IPKCS12;
use Uninett\LetsWifi\X509\ICertificate;

class EapTlsMethod implements IAuthenticationMethod
{
	/** @var string */
	private $anonymousIdentity;

	/** @var ICertificate[] */
	private $cas;

	/** @var ?IPKCS12 */
	private $clientCertificate;

	/** @var ?string */
	private $clientCertificatePassPhrase;

	/**
	 * @param string         $anonymousIdentity           The anonymous identity for this profile, if null it's determined from the user credentials
	 * @param ICertificate[] $cas                         The accepted certificates for this
	 * @param ?IPKCS12       $clientCertificate           User credential
	 * @param ?string        $clientCertificatePassPhrase Passphrase for $clientCertificate
	 */
	public function __construct( string $anonymousIdentity, array $cas, IPKCS12 $clientCertificate = null, $clientCertificatePassPhrase = null )
	{
		$this->anonymousIdentity = $anonymousIdentity;
		$this->cas = \array_values( $cas );
		$this->clientCertificate = $clientCertificate;
		$this->clientCertificatePassPhrase = $clientCertificatePassPhrase;
	}

	/** {@inheritdoc} */
	public function getCACertificates(): array
	{
		return $this->cas;
	}

	/**
	 * Get the PKCS12 certificate used for authentication
	 *
	 * If this is not provided, the user must be asked for a PKCS12 file
	 * when installing a profile with this authentication method.
	 *
	 * @return ?IPKCS12 The user credential
	 */
	public function getPKCS12(): ?IPKCS12
	{
		return $this->clientCertificate;
	}

	/**
	 * Get the passphrase for the PKCS12 certificate
	 *
	 * If this is not provided, the user must be asked for this passphrase
	 * when installing a profile with this authentication method.
	 *
	 * @return string? The passphrase
	 */
	public function getPKCS12PassPhrase(): ?string
	{
		return $this->clientCertificatePassPhrase;
	}

	/** {@inheritdoc} */
	public function getAnonymousIdentity(): string
	{
		return $this->anonymousIdentity;
	}
}
