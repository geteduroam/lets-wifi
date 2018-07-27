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

class PKCS12 implements IPKCS12
{
	/** @var string */
	private $bytes;

	/**
	 * @param string $bytes Bytes of a PKCS12 container
	 */
	public function __construct( string $bytes )
	{
		$this->bytes = $bytes;
	}

	/** {@inheritdoc} */
	public function getBytes(): string
	{
		return $this->bytes;
	}
}
