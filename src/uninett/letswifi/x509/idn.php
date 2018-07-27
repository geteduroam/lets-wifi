<?php declare(strict_types=1);

/**
 * Distinguished Name
 *
 * Composes the $dn parameter of openssl_csr_new().
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

interface IDN
{
	/**
	 * Generate a string with all provided subject fields
	 *
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string String representation of the subject fields (DN)
	 */
	public function getSubjectString(): string;

	/**
	 * Get all subject fields as an array
	 *
	 * @return array String to string mapped array of all subject fields
	 */
	public function getSubjectFields(): array;
}
