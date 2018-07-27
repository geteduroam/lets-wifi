<?php declare(strict_types=1);

/**
 * CSR - Certificate Signing Request
 *
 * Composes openssl_csr_new
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

interface ICSR
{
	/**
	 * Get the DN for the CSR
	 *
	 * @return IDN
	 */
	public function getDN(): IDN;

	/**
	 * Get the private key in this CSR
	 *
	 * @return IPrivateKey
	 */
	public function getPrivateKey(): IPrivateKey;

	/**
	 * Get the configuration arguments that were used to create this CSR
	 *
	 * @return IKeyConfig
	 */
	public function getConfigArgs(): IKeyConfig;

	/**
	 * Get the native PHP resource for this CSR
	 *
	 * @return resource The native PHP resource for this CSR
	 */
	public function getResource();
}
