<?php declare(strict_types=1);

/**
 * Key configuration
 *
 * Composes the configuration array for openssl_csr_new() and openssl_pkey_new()
 *
 * @see http://php.net/manual/en/function.openssl-csr-new.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

interface IKeyConfig
{
	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getDigestAlg(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getX509Extensions(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getReqExtensions(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getPrivateKeyBits(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return EOpensslKeyType
	 */
	public function getPrivateKeyType(): ?EOpensslKeyType;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getEncryptKey(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getEncryptKeyCipher(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getCurveName(): ?string;

	/**
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return string
	 */
	public function getConfig(): ?string;

	/**
	 * Get the configuration array that can be used as $configargs in openssl_csr_new or openssl_pkey_new
	 *
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 * @see http://php.net/manual/en/function.openssl-pkey-new.php
	 *
	 * @return array configargs array
	 */
	public function toArray(): array;

	/**
	 * Get extra attributes for CSR
	 *
	 * @see http://php.net/manual/en/function.openssl-csr-new.php
	 *
	 * @return array extra CSR attributes
	 */
	public function getExtraAttribs(): array;
}
