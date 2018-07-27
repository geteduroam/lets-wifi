<?php declare(strict_types=1);

/**
 * CSR - Certificate Signing Request
 *
 * @see http://php.net/manual/en/function.openssl-csr-new.php
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

class CSR implements ICSR
{
	/** @var IDN */
	private $dn;

	/** @var IPrivateKey */
	private $key;

	/** @var IKeyConfig */
	private $config;

	/** @var null|resource */
	private $csrResource;

	/**
	 * @param IDN         $dn
	 * @param IPrivateKey $key
	 * @param IKeyConfig  $config
	 */
	public function __construct( IDN $dn, IPrivateKey $key, IKeyConfig $config )
	{
		$this->dn = $dn;
		$this->key = $key;
		$this->config = $config;
	}

	/** {@inheritdoc} */
	public function getDN(): IDN
	{
		return $this->dn;
	}

	/** {@inheritdoc} */
	public function getPrivateKey(): IPrivateKey
	{
		return $this->key;
	}

	/** {@inheritdoc} */
	public function getConfigArgs(): IKeyConfig
	{
		return $this->config;
	}

	/** {@inheritdoc} */
	public function getResource()
	{
		if ( null === $this->csrResource ) {
			/** @var resource */
			$privKey = $this->getPrivateKey()->getResource();
			OpenSSLException::flushErrorMessages();
			$csr = \openssl_csr_new(
					$this->getDN()->getSubjectFields(),
					$privKey,
					$this->getConfigArgs()->toArray()
					//$this->getConfigArgs()->getExtraAttribs() // @TODO what is the default value here?
				);
			if ( false === $csr ) {
				throw new OpenSSLException();
			}
			$this->csrResource = $csr;
		}

		return $this->csrResource;
	}
}
