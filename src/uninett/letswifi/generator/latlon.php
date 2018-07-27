<?php declare(strict_types=1);

/**
 * Latitude and longitude implementation
 *
 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

class LatLon implements ILatLon
{
	/** @var float */
	private $lon;

	/** @var float */
	private $lat;

	/**
	 * Create a new point
	 *
	 * @param float $lat Latitude
	 * @param float $lon Longitude
	 */
	public function __construct( float $lat, float $lon )
	{
		$this->lat = $lat;
		$this->lon = $lon;
	}

	/**
	 * Shorthand for generateEapConfigXml()
	 *
	 * @see ILatLon::generateEapConfigXml()
	 *
	 * @return string XML snippet for Eap Config
	 */
	public function __toString(): string
	{
		return $this->generateEapConfigXml();
	}

	/** {@inheritdoc} */
	public function generateEapConfigXml(): string
	{
		return \sprintf( '<Longitude>%d</Longitude><Latitude>%d</Latitude>', $this->lon, $this->lat );
	}

	/** {@inheritdoc} */
	public function getLongitude(): float
	{
		return $this->lon;
	}

	/** {@inheritdoc} */
	public function getLatitude(): float
	{
		return $this->lat;
	}
}
