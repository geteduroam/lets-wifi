<?php declare(strict_types=1);

/**
 * Profile meta data
 *
 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

interface IProfileMetadata
{
	/**
	 * Get the display name
	 *
	 * From the Eap-Metadata draft:
	 * 'DisplayName' specifies a user-friendly name for the EAP Identity
	 * Provider.
	 *
	 * @return string
	 */
	public function getDisplayName(): string;

	/**
	 * Get the description
	 *
	 * From the Eap-Metadata draft:
	 * 'Description' specifies a generic descriptive text which should be
	 * displayed to the user prior to the installation of the
	 * configuration data.
	 *
	 * @return ?string
	 */
	public function getDescription(): ?string;

	/**
	 * Get the provider's location
	 *
	 * From the Eap-Metadata draft:
	 * 'ProviderLocation' specifies the approximate geographic
	 * location(s) of the EAP Identity Provider and/or his Points of
	 * Presence.
	 *
	 * @return ?ILatLon
	 */
	public function getProviderLocation(): ?ILatLon;

	/**
	 * Get the provider's logo
	 *
	 * From the Eap-Metadata draft:
	 * 'ProviderLogo' specifies the logo of the EAP Identity Provider.
	 *
	 * @return ?ILogo
	 */
	public function getProviderLogo(): ?ILogo;

	/**
	 * Get the profile's terms of use
	 *
	 * From the Eap-Metadata draft:
	 * 'TermsOfUse' contains terms of use to be displayed to and
	 * acknowledged by the user prior to the installation of the
	 * configuration on the user's system
	 *
	 * @return ?string
	 */
	public function getTermsOfUse(): ?string;

	/**
	 * Get the provider's helpdesk contact details
	 *
	 * From the Eap-Metadata draft:
	 * 'Helpdesk' is a container with three possible sub-elements:
	 * 'EmailAddress', 'WebAddress' and 'Phone', all of which can be
	 * displayed to the user and possibly retained for future debugging
	 * hints.
	 *
	 * @return ?IHelpdesk
	 */
	public function getHelpDesk(): ?IHelpdesk;
}
