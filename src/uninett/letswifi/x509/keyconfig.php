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

class KeyConfig implements IKeyConfig
{
	/** @var array */
	private $configargs;

	/** @var array */
	private $extraattribs;

	/**
	 * Create new key configuration
	 *
	 * @param array $configargs   Array like the argument to openssl_csr_new() with the same name
	 * @param array $extraattribs Array like the argument to openssl_csr_new() with the same name
	 */
	public function __construct( array $configargs = [], array $extraattribs = [] )
	{
		if ( !\array_key_exists( 'config', $configargs ) ) {
			$configargs['config'] = __DIR__ . \DIRECTORY_SEPARATOR . 'openssl.cnf';
		}

		$this->configargs = $configargs;
		$this->extraattribs = $extraattribs;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getCurveName()
	 */
	public function getCurveName(): ?string
	{
		return $this->configargs['curve_name'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getX509Extensions()
	 */
	public function getX509Extensions(): ?string
	{
		return $this->configargs['x509_extensions'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getExtraAttribs()
	 */
	public function getExtraAttribs(): array
	{
		return $this->extraattribs;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getReqExtensions()
	 */
	public function getReqExtensions(): ?string
	{
		return $this->configargs['req_extensions'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getPrivateKeyType()
	 */
	public function getPrivateKeyType(): ?EOpensslKeyType
	{
		if ( \array_key_exists( 'private_key_type', $this->configargs ) ) {
			return new EOpensslKeyType( $this->configargs['private_key_type'] );
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getEncryptKeyCipher()
	 */
	public function getEncryptKeyCipher(): ?string
	{
		return $this->configargs['encrypt_key_cipher'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getEncryptKey()
	 */
	public function getEncryptKey(): ?string
	{
		return $this->configargs['encrypt_key'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::toArray()
	 */
	public function toArray(): array
	{
		return $this->configargs;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getPrivateKeyBits()
	 */
	public function getPrivateKeyBits(): ?string
	{
		return $this->configargs['private_key_bits'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getDigestAlg()
	 */
	public function getDigestAlg(): ?string
	{
		return $this->configargs['digest_alg'];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Uninett\LetsWifi\X509\IKeyConfig::getConfig()
	 */
	public function getConfig(): ?string
	{
		return $this->configargs['config'];
	}
}
