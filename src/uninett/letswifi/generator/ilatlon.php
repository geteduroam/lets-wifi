<?php declare(strict_types=1);

/**
 * Latitude and longitude
 *
 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

interface ILatLon
{
	/**
	 * Get the latitude
	 *
	 * @return float
	 */
	public function getLatitude(): float;

	/**
	 * Get the longitude
	 *
	 * @return float
	 */
	public function getLongitude(): float;

	/**
	 * Generate an XML snippet for the Eap Config XML
	 *
	 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
	 *
	 * @return string Valid XML snippet to be used in Eap Config XML
	 */
	public function generateEapConfigXml(): string;
}
