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

interface IPrivateKey extends IKey
{
	/**
	 * Export payload as password-protected PEM-encoded string.
	 */
	public function exportPEM( string $password, ?IKeyConfig $configArgs = null ): string;

	/**
	 * Export payload as PEM-encoded string.
	 */
	public function exportPEMWithoutPassword( ?IKeyConfig $configArgs = null ): string;
}
