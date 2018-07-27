<?php declare(strict_types=1);

/**
 * Profile metadata
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

class ProfileMetadata implements IProfileMetadata
{
	/** @var ?ILatLon */
	private $providerLocation;

	/** @var ?string */
	private $termsOfUse;

	/** @var string */
	private $displayName;

	/** @var ?ILogo */
	private $providerLogo;

	/** @var ?string */
	private $description;

	/** @var ?IHelpdesk */
	private $helpdesk = null;

	/**
	 * Construct new profile metadata
	 *
	 * @param string $displayName
	 * @param string $description
	 */
	public function __construct( string $displayName, ?string $description = null )
	{
		$this->displayName = $displayName;
		$this->description = $description;
	}

	/** {@inheritdoc} */
	public function getProviderLocation(): ?ILatLon
	{
		return $this->providerLocation;
	}

	/**
	 * Set the geographical location for the provider behind this profile
	 *
	 * @param float $lat Latitude
	 * @param float $lon Longitude
	 */
	public function setProviderLatLon( float $lat, float $lon ): void
	{
		$this->providerLocation = new LatLon( $lat, $lon );
	}

	/** {@inheritdoc} */
	public function getTermsOfUse(): ?string
	{
		return $this->termsOfUse;
	}

	/**
	 * Set the Terms of Use string for the profile
	 *
	 * @param string $termsOfUse
	 */
	public function setTermsOfUse( ?string $termsOfUse ): void
	{
		$this->termsOfUse = $termsOfUse;
	}

	/** {@inheritdoc} */
	public function getDisplayName(): string
	{
		return $this->displayName;
	}

	/**
	 * Set the display name for this profile
	 *
	 * Note that the constructor already requires this.
	 *
	 * @param string $displayName
	 */
	public function setDisplayName( string $displayName ): void
	{
		$this->displayName = $displayName;
	}

	/** {@inheritdoc} */
	public function getProviderLogo(): ?ILogo
	{
		return $this->providerLogo;
	}

	/**
	 * Set the logo of the provider behind the profile
	 *
	 * @param ILogo $providerLogo
	 */
	public function setProviderLogo( ?ILogo $providerLogo ): void
	{
		$this->providerLogo = $providerLogo;
	}

	/** {@inheritdoc} */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * Set the description for this profile
	 *
	 * Note that you can also set this in the constructor.
	 *
	 * @param string $description
	 */
	public function setDescription( ?string $description ): void
	{
		$this->description = $description;
	}

	/** {@inheritdoc} */
	public function getHelpDesk(): ?IHelpdesk
	{
		return $this->helpdesk;
	}

	/**
	 * Set the contact details for the helpdesk of the provider behind this profile
	 *
	 * The fields that are not applicable can be set to null.
	 *
	 * @param ?string $mail  E-mail address of the help desk
	 * @param ?string $web   URL with support information
	 * @param ?string $phone Phone number of the help desk
	 */
	public function setHelpDeskMailWebPhone( ?string $mail, ?string $web, ?string $phone ): void
	{
		$this->helpdesk = new Helpdesk( $mail, $web, $phone );
	}
}
