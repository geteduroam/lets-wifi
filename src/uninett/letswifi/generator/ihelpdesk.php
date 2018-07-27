<?php declare(strict_types=1);

/**
 * Helpdesk contact details
 *
 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

interface IHelpdesk
{
	/**
	 * Get the e-mail address for the helpdesk
	 *
	 * @return ?string
	 */
	public function getEmailAddress(): ?string;

	/**
	 * Get the web address for the helpdesk
	 *
	 * @return ?string
	 */
	public function getWebAddress(): ?string;

	/**
	 * Get the phone number for the helpdesk
	 *
	 * @return ?string
	 */
	public function getPhone(): ?string;

	/**
	 * Generate an XML snippet for the Eap Config XML
	 *
	 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
	 *
	 * @return string Valid XML snippet to be used in Eap Config XML
	 */
	public function generateEapConfigXml(): string;
}
