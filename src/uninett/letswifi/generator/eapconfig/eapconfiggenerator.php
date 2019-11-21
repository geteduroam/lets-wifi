<?php declare(strict_types=1);

/**
 * Eap Config profile generator
 *
 * @see https://tools.ietf.org/html/draft-winter-opsawg-eap-metadata-02
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator\EapConfig;

use Uninett\LetsWifi\Authentication\EapTlsMethod;
use Uninett\LetsWifi\Generator\AbstractProfileGenerator;

class EapConfigGenerator extends AbstractProfileGenerator
{
	/** {@inheritdoc} */
	public function __toString(): string
	{
		$result = '';
		$result .= '<?xml version="1.0" encoding="utf-8"?>'
			. "\r\n" . '<EAPIdentityProviderList xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eap-metadata.xsd">'
			. "\r\n" . '<EAPIdentityProvider ID="fyrkat.no" namespace="urn:RFC4282:realm" lang="en" version="1">'
			. "\r\n" . '<AuthenticationMethods>'
			;
		foreach ( $this->authentication as $authentication ) {
			if ( $authentication instanceof EapTlsMethod ) {
				$result .= $this->generateEapTlsData( $authentication );
			}
		}
		$result .= ''
			. "\r\n" . '</AuthenticationMethods>'
			. "\r\n" . '<CredentialApplicability>'
			. "\r\n" . '<IEEE80211>'
			. "\r\n" . '<SSID>eduroam</SSID>'
			. "\r\n" . '<MinRSNProto>CCMP</MinRSNProto>'
			. "\r\n" . '</IEEE80211>'
			. "\r\n" . '<IEEE80211>'
			. "\r\n" . '<ConsortiumOID>001bc50460</ConsortiumOID>'
			. "\r\n" . '</IEEE80211>'
			. "\r\n" . '</CredentialApplicability>'
			. "\r\n" . '<ProviderInfo>'
			. "\r\n" . '<DisplayName>' . \htmlspecialchars( $this->metadata->getDisplayName() ) . '</DisplayName>'
			. "\r\n";
		if ( null !== $description = $this->metadata->getDescription() ) {
			$result .= ''
				. "\r\n" . '<Description>' . \htmlspecialchars( $description ) . '</Description>'
				. "\r\n";
		}
		if ( null !== $loc = $this->metadata->getProviderLocation() ) {
			$result .= ''
				. "\r\n" . '<ProviderLocation>' . $loc->generateEapConfigXml() . '</ProviderLocation>'
				. "\r\n";
		}
		if ( null !== $logo = $this->metadata->getProviderLogo() ) {
			$result .= ''
				. "\r\n" . '<ProviderLogo mime="' . $logo->getContentType() . '" encoding="base64">' . \base64_encode( $logo->getBytes() ) . '</ProviderLocation>'
				. "\r\n";
		}
		if ( null !== $tos = $this->metadata->getTermsOfUse() ) {
			$result .= ''
				. "\r\n" . '<TermsOfUse>' . \htmlspecialchars( $tos ) . '</TermsOfUse>'
				. "\r\n";
		}
		if ( null !== $helpdesk = $this->metadata->getHelpDesk() ) {
			$result .= ''
				. "\r\n" . '<Helpdesk>' . $helpdesk->generateEapConfigXml() . '</Helpdesk>'
				. "\r\n";
		}
		$result .= ''
			. "\r\n" . '</ProviderInfo>'
			. "\r\n" . '</EAPIdentityProvider>'
			. "\r\n" . '</EAPIdentityProviderList>'
			. "\r\n";

		return $result;
	}

	/** {@inheritdoc} */
	public function getContentType(): string
	{
		// There is no reference to this, and no official registration,
		// but this is consistent with the CAT website.
		// Even though unregistered content types should use the x- prefix.
		return 'application/eap-config';
	}

	/**
	 * Generate Apple Mobile Configuration data for EAP-TLS authentication
	 *
	 * @return string PLIST portion for wifi and certificates, to be used in a mobileconfig
	 */
	private function generateEapTlsData( EapTlsMethod $authentication )
	{
		$result = '';
		$result .= ''
			. "\r\n" . '<AuthenticationMethod>'
			. "\r\n" . '<EAPMethod>'
			. "\r\n" . '<Type>13</Type>'
			. "\r\n" . '</EAPMethod>'
			. "\r\n" . '<ServerSideCredential>'
			. "\r\n";
		foreach ( $authentication->getCACertificates() as $ca ) {
			$result .= ''
				. "\r\n" . '<CA format="X.509" encoding="base64">' . \base64_encode( $ca->getPEMBytes() ) . '</CA>'
				. "\r\n";
		}
		// @TODO server names
		$result .= ''
			. "\r\n" . '</ServerSideCredential>'
			. "\r\n";
		if ( null === $pkcs12 = $authentication->getPKCS12() ) {
			$result .= ''
				. "\r\n" . '<ClientSideCredential/>'
				. "\r\n";
		} else {
			$result .= ''
				. "\r\n" . '<ClientSideCredential>'
				. "\r\n" . '<AnonymousIdentity>' . \htmlspecialchars( $authentication->getAnonymousIdentity() ) . '</AnonymousIdentity>'
				. "\r\n" . '<ClientCertificate>' . \base64_encode( $pkcs12->getBytes() ) . '</ClientCertificate>'
				. "\r\n";
			if ( null !== $passphrase = $authentication->getPKCS12PassPhrase() ) {
				$result .= ''
					. "\r\n" . '<Passphrase>' . \htmlspecialchars( $passphrase ) . '</Passphrase>'
					. "\r\n";
			}
			$result .= ''
				. "\r\n" . '</ClientSideCredential>'
				. "\r\n";
		}
		$result .= ''
				. "\r\n" . '</AuthenticationMethod>'
				. "\r\n";

		return $result;
	}
}
