<?php declare(strict_types=1);

/**
 * Certificate
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

interface ICertificate extends IPublicKey
{
	/**
	 * Get the private key associated to this certificate
	 *
	 * @return ?IPrivateKey
	 */
	public function getPrivateKey(): ?IPrivateKey;

	/**
	 * Export this certificate along with the private key
	 *
	 * @param string $passphrase Passphrase to encrypt the resulting PKCS12 with
	 *
	 * @return IPKCS12
	 */
	public function exportPKCS12WithPrivateKey( string $passphrase ): IPKCS12;

	/**
	 * Get the chain of CAs that signed this certificate
	 *
	 * @return ICertificate[]
	 */
	public function getChain(): array;

	/**
	 * Export payload as PEM-encoded string
	 *
	 * @param bool $text Also output human readable text
	 *
	 * @return string Newline separated string containing ASCII armored PEM encoded data
	 */
	public function getPEMBytes( bool $text = false ): string;
}
