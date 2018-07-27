<?php declare(strict_types=1);

/**
 * OpenSSL key types
 *
 * All valid values for the key type returned from openssl_pkey_get_details
 *
 * @see http://php.net/manual/en/function.openssl-pkey-get-details.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

final class EOpensslKeyType /* extends SplEnum */
{
	const __default = self::UNKNOWN;

	const UNKNOWN = -1;

	const OPENSSL_KEYTYPE_RSA = \OPENSSL_KEYTYPE_RSA;

	const OPENSSL_KEYTYPE_DSA = \OPENSSL_KEYTYPE_DSA;

	const OPENSSL_KEYTYPE_DH = \OPENSSL_KEYTYPE_DH;

	const OPENSSL_KEYTYPE_EC = \OPENSSL_KEYTYPE_EC;

	/** @var int */
	private $value;

	/**
	 * Get ENUM instance
	 *
	 * @param int $value The value
	 */
	public function __construct( int $value )
	{
		$this->value = $value;
	}

	public function __toString(): string
	{
		switch ( $this->value ) {
			case self::OPENSSL_KEYTYPE_RSA: return 'OPENSSL_KEYTYPE_RSA';
			case self::OPENSSL_KEYTYPE_DSA: return 'OPENSSL_KEYTYPE_DSA';
			case self::OPENSSL_KEYTYPE_DH: return 'OPENSSL_KEYTYPE_DH';
			case self::OPENSSL_KEYTYPE_EC: return 'OPENSSL_KEYTYPE_EC';
			default: return 'UNKNOWN';
		}
	}
}
