<?php declare(strict_types=1);

/**
 * PKCS12 container
 *
 * @see http://php.net/manual/en/function.openssl-pkcs12-export.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

interface IPKCS12
{
	/**
	 * Output the PKCS12 store
	 */
	public function getBytes(): string;

	/**
	 * Get the certificate from this container (including private key and chain)
	 *
	 * @param string $password The password the container is encrypted with
	 *
	 * @return ICertificate The certificate
	 */
	public function getCertificate( ?string $password ): ICertificate;
}
