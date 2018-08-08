<?php declare(strict_types=1);

/**
 * PKCS12 Config profile generator
 *
 * @see https://en.wikipedia.org/wiki/PKCS_12
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator\PKCS12;

use Uninett\LetsWifi\Authentication\EapTlsMethod;
use Uninett\LetsWifi\Generator\AbstractProfileGenerator;

class PKCS12ConfigGenerator extends AbstractProfileGenerator
{
	/** {@inheritdoc} */
	public function __toString(): string
	{
		foreach ( $this->authentication as $authentication ) {
			if ( $authentication instanceof EapTlsMethod ) {
				$pkcs = $authentication->getPKCS12();
				if ( null === $pkcs ) {
					throw new \DomainException( 'Unable to generate profile, as no certificate is included' );
				}

				return $pkcs->getBytes();
			}
		}
		throw new \DomainException( 'Unable to generate profile, as no PKCS12 authentication is possible' );
	}

	/** {@inheritdoc} */
	public function getContentType(): string
	{
		return 'application/x-pkcs12';
	}
}
