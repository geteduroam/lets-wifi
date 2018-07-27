<?php declare(strict_types=1);

/**
 * Helpdesk contact details implementation
 *
 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

class Helpdesk implements IHelpdesk
{
	/** @var ?string */
	private $mail;

	/** @var ?string */
	private $web;

	/** @var ?string */
	private $phone;

	/**
	 * Create a new helpdesk contact point
	 *
	 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02#section-2.2.3
	 *
	 * @param string $mail
	 * @param string $web
	 * @param string $phone
	 */
	public function __construct( ?string $mail, ?string $web, ?string $phone )
	{
		$this->mail = $mail;
		$this->web = $web;
		$this->phone = $phone;
	}

	/**
	 * Shorthand for generateEapConfigXml()
	 *
	 * @see IHelpdesk::generateEapConfigXml()
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
		if ( null === $this->mail && null === $this->web && null === $this->phone ) {
			return '';
		}

		$result = '<Helpdesk>';
		if ( null === $this->mail ) {
			$result .= '<EmailAddress/>';
		} else {
			$result .= \sprintf( '<EmailAddress>%s</EmailAddress>', \htmlspecialchars( $this->mail ) );
		}
		if ( null === $this->web ) {
			$result .= '<WebAddress/>';
		} else {
			$result .= \sprintf( '<WebAddress>%s</WebAddress>', \htmlspecialchars( $this->web ) );
		}
		if ( null === $this->phone ) {
			$result .= '<Phone/>';
		} else {
			$result .= \sprintf( '<Phone>%s</Phone>', \htmlspecialchars( $this->phone ) );
		}

		return $result;
	}

	/** {@inheritdoc} */
	public function getEmailAddress(): ?string
	{
		return $this->mail;
	}

	/** {@inheritdoc} */
	public function getWebAddress(): ?string
	{
		return $this->web;
	}

	/** {@inheritdoc} */
	public function getPhone(): ?string
	{
		return $this->phone;
	}
}
