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
}
