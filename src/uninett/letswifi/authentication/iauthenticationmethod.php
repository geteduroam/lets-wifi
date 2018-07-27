<?php declare(strict_types=1);

/**
 * Authentication method
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Authentication;

use Uninett\LetsWifi\X509\ICertificate;

interface IAuthenticationMethod
{
	/**
	 * Get the valid CA certificates (server-side) for this authentication method
	 *
	 * @return ICertificate[] The certificates
	 */
	public function getCACertificates(): array;

	/**
	 * Get the outer identity for the RADIUS conversation
	 *
	 * @return string
	 */
	public function getAnonymousIdentity(): string;
}
