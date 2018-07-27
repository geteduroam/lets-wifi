<?php declare(strict_types=1);

/**
 * Private key
 *
 * @see http://php.net/manual/en/function.openssl-pkey-get-details.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

interface IKey
{
	/**
	 * Get the PHP native resource.
	 *
	 * @return resource The PHP native resource
	 */
	public function getResource();

	/**
	 * Placeholder.
	 */
	public function getBits(): int;

	/**
	 * Placeholder.
	 */
	public function getPublicKey(): IPublicKey;

	/**
	 * Placeholder.
	 */
	public function getType(): EOpensslKeyType;
}
