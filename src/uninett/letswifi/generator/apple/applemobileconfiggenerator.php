<?php declare(strict_types=1);

/**
 * Apple mobileconfig profile generator
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator\Apple;

use Uninett\LetsWifi\UUID;
use Uninett\LetsWifi\Authentication\EapTlsMethod;
use Uninett\LetsWifi\Generator\AbstractProfileGenerator;

class AppleMobileConfigGenerator extends AbstractProfileGenerator
{
	/** @var ?UUID */
	private $profileUuid = null;

	/** @var AppleMobileConfigWifiAuthentication[] */
	private $authenticationObjects = [];

	/** {@inheritdoc} */
	public function __toString(): string
	{
		if ( null === $this->profileUuid ) {
			$this->profileUuid = new UUID();
		}

		if ( empty( $this->authenticationObjects ) ) {
			$this->authenticationObjects = [];
			foreach ( $this->authentication as $authentication ) {
				$this->authenticationObjects[] = new AppleMobileConfigWifiAuthentication( $authentication );
			}
		}

		$result = '<?xml version="1.0" encoding="UTF-8"?>'
			. "\r\n" . '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">'
			. "\r\n" . '<plist version="1.0">'
			. "\r\n" . '<dict>'
			. "\r\n" . '	<key>PayloadContent</key>'
			. "\r\n" . '	<array>'
			. "\r\n";
		foreach ( $this->authenticationObjects as $authentication ) {
			if ( $authentication->getAuthentication() instanceof EapTlsMethod ) {
				$result .= $this->generateEapTlsData( $authentication );
			} else {
				throw new \DomainException( 'Cannot generate Apple Mobile configuration for given authentication method' );
			}
		}
		$result .= ''
			. "\r\n" . '	</array>'
			. "\r\n" . '	<key>PayloadDescription</key>'
			. "\r\n" . '	<string>Let\'s Wifi</string>'
			. "\r\n" . '	<key>PayloadDisplayName</key>'
			. "\r\n" . '	<string>eduroam</string>'
			. "\r\n" . '	<key>PayloadIdentifier</key>'
			// @TODO make this configurable?
			. "\r\n" . '	<string>no.eduroam.letswifi.' . $this->profileUuid . '</string>'
			. "\r\n" . '	<key>PayloadOrganization</key>'
			. "\r\n" . '	<string>' . \htmlspecialchars( $this->metadata->getDisplayName() ) . '</string>'
			. "\r\n" . '	<key>PayloadRemovalDisallowed</key>'
			. "\r\n" . '	<false/>'
			. "\r\n" . '	<key>PayloadType</key>'
			. "\r\n" . '	<string>Configuration</string>'
			. "\r\n" . '	<key>PayloadUUID</key>'
			. "\r\n" . '	<string>' . $this->profileUuid . '</string>'
			. "\r\n" . '	<key>PayloadVersion</key>'
			. "\r\n" . '	<integer>1</integer>'
			. "\r\n";
		if ( null !== $tos = $this->metadata->getTermsOfUse() ) {
			$result .= ''
				. "\r\n" . '	<key>ConsentText</key>'
				. "\r\n" . '	<dict>'
				. "\r\n" . '		<key>default</key>'
				. "\r\n" . '		<string>' . \htmlspecialchars( $tos ) . '</string>'
				. "\r\n" . '	</dict>'
				. "\r\n";
		}
		$result .= ''
			. "\r\n" . '</dict>'
			. "\r\n" . '</plist>'
			. "\r\n";

		return $result;
	}

	/** {@inheritdoc} */
	public function getContentType(): string
	{
		return 'application/x-apple-aspen-config';
	}

	/**
	 * Generate Apple Mobile Configuration data for EAP-TLS authentication
	 *
	 * @return string PLIST portion for wifi and certificates, to be used in a mobileconfig
	 */
	private function generateEapTlsData( AppleMobileConfigWifiAuthentication $authentication )
	{
		if ( null === $this->profileUuid ) {
			$this->profileUuid = new UUID();
		}

		/** @var EapTlsMethod */
		$eap = $authentication->getAuthentication();

		$result = '';
		$result .= ''
			. "\r\n" . '		<dict>'
			. "\r\n" . '			<key>AutoJoin</key>'
			. "\r\n" . '			<true/>'
			. "\r\n" . '			<key>EAPClientConfiguration</key>'
			. "\r\n" . '			<dict>'
			. "\r\n" . '				<key>AcceptEAPTypes</key>'
			. "\r\n" . '				<array>'
			. "\r\n" . '					<integer>13</integer>'
			. "\r\n" . '				</array>'
			. "\r\n" . '				<key>EAPFASTProvisionPAC</key>'
			. "\r\n" . '				<false/>'
			. "\r\n" . '				<key>EAPFASTProvisionPACAnonymously</key>'
			. "\r\n" . '				<false/>'
			. "\r\n" . '				<key>EAPFASTUsePAC</key>'
			. "\r\n" . '				<false/>'

			. "\r\n" . '				<key>PayloadCertificateAnchorUUID</key>'
			. "\r\n" . '				<array>'
		// @TODO support multiple CAs
			. "\r\n" . '					<string>' . $authentication->getCaUuid() . '</string>'
			. "\r\n" . '				</array>'

			. "\r\n" . '				<key>PayloadCertificatecaUuid</key>'
			. "\r\n" . '				<array>'
		// @TODO support multiple CAs
			. "\r\n" . '					<string>' . $authentication->getCaUuid() . '</string>'
			. "\r\n" . '				</array>'
			. "\r\n" . '				<key>UserName</key>'
			. "\r\n" . '				<string>' . \htmlspecialchars( $eap->getAnonymousIdentity() ) . '</string>'
			. "\r\n" . '			</dict>'
			. "\r\n" . '			<key>EncryptionType</key>'
			. "\r\n" . '			<string>WPA</string>'
			. "\r\n" . '			<key>HIDDEN_NETWORK</key>'
			. "\r\n" . '			<false/>'
			. "\r\n" . '			<key>PayloadCertificateUUID</key>'
			. "\r\n" . '			<string>' . $authentication->getCertUuid() . '</string>'
			. "\r\n";
		if ( null !== $description = $this->metadata->getDescription() ) {
			$result .= ''
				. "\r\n" . '			<key>PayloadDescription</key>'
				. "\r\n" . '			<string>' . \htmlspecialchars( $description ) . '</string>'
				. "\r\n";
		}
		$result .= ''
			. "\r\n" . '			<key>PayloadDisplayName</key>'
			. "\r\n" . '			<string>Wi-Fi</string>'
			. "\r\n" . '			<key>PayloadIdentifier</key>'
			. "\r\n" . '			<string>com.apple.wifi.managed.' . $authentication->getCertUuid() . '</string>'
			. "\r\n" . '			<key>PayloadOrganization</key>'
			. "\r\n" . '			<string>' . \htmlspecialchars( $this->metadata->getDisplayName() ) . '</string>'
			. "\r\n" . '			<key>PayloadType</key>'
			. "\r\n" . '			<string>com.apple.wifi.managed</string>'
			. "\r\n" . '			<key>PayloadUUID</key>'
			. "\r\n" . '			<string>' . $authentication->getWifiUuid() . '</string>'
			. "\r\n" . '			<key>PayloadVersion</key>'
			. "\r\n" . '			<integer>1</integer>'
			. "\r\n" . '			<key>ProxyType</key>'
			. "\r\n" . '			<string>None</string>'
			. "\r\n" . '			<key>SSID_STR</key>'
			. "\r\n" . '			<string>eduroam</string>'
			. "\r\n" . '		</dict>'
			. "\r\n" . '		<dict>'
			. "\r\n" . '			<key>PayloadCertificateFileName</key>'
			. "\r\n" . '			<string>ca.cer</string>'
			. "\r\n" . '			<key>PayloadContent</key>'
			. "\r\n" . '			<data>'
			. "\r\n";
		// @TODO support multiple CAs
		foreach ( \str_split( $eap->getCACertificates()[0]->getPEMBytes(), 39 ) as $line ) {
			$result .= "\t\t\t\t" . \base64_encode( $line ) . "\r\n";
		}
		$result .= ''
			. "\r\n" . '			</data>'
			. "\r\n" . '			<key>PayloadDescription</key>'
			. "\r\n" . '			<string>Provides device authentication (certificate or identity ).</string>'
			. "\r\n" . '			<key>PayloadDisplayName</key>'
			. "\r\n" . '			<string>UNINETT geteduroam Certificate Authority</string>'
			. "\r\n" . '			<key>PayloadIdentifier</key>'
			. "\r\n" . '			<string>com.apple.security.root.' . $authentication->getCertUuid() . '</string>'
			. "\r\n" . '			<key>PayloadOrganization</key>'
			. "\r\n" . '			<string>' . \htmlspecialchars( $this->metadata->getDisplayName() ) . '</string>'
			. "\r\n" . '			<key>PayloadType</key>'
			. "\r\n" . '			<string>com.apple.security.root</string>'
			. "\r\n" . '			<key>PayloadUUID</key>'
			. "\r\n" . '			<string>' . $authentication->getCaUuid() . '</string>'
			. "\r\n" . '			<key>PayloadVersion</key>'
			. "\r\n" . '			<integer>1</integer>'
			. "\r\n" . '		</dict>'
			. "\r\n";
		$pkcs12 = $eap->getPKCS12();
		if ( null !== $pkcs12 ) {
			$result .= ''
				. "\r\n" . '		<dict>'
				. "\r\n" . '			<key>Password</key>'
				. "\r\n" . '			<string>password</string>'
				. "\r\n" . '			<key>PayloadCertificateFileName</key>'
				. "\r\n" . '			<string>' . $authentication->getCertUuid() . '.p12</string>'
				. "\r\n" . '			<key>PayloadContent</key>'
				. "\r\n" . '			<data>'
				. "\r\n";
			foreach ( \str_split( $pkcs12->getBytes(), 39 ) as $line ) {
				$result .= "\t\t\t\t" . \base64_encode( $line ) . "\r\n";
			}
			$result .= ''
				. "\r\n" . '			</data>'
				. "\r\n" . '			<key>PayloadDescription</key>'
				. "\r\n" . '			<string>Provides device authentication (certificate or identity ).</string>'
				. "\r\n" . '			<key>PayloadDisplayName</key>'
				. "\r\n" . '			<string>' . $authentication->getCertUuid() . '</string>'
				. "\r\n" . '			<key>PayloadIdentifier</key>'
				. "\r\n" . '			<string>com.apple.security.pkcs12.' . $authentication->getCertUuid() . '</string>'
				. "\r\n" . '			<key>PayloadOrganization</key>'
				. "\r\n" . '			<string>' . \htmlspecialchars( $this->metadata->getDisplayName() ) . '</string>'
				. "\r\n" . '			<key>PayloadType</key>'
				. "\r\n" . '			<string>com.apple.security.pkcs12</string>'
				. "\r\n" . '			<key>PayloadUUID</key>'
				. "\r\n" . '			<string>' . $authentication->getCertUuid() . '</string>'
				. "\r\n" . '			<key>PayloadVersion</key>'
				. "\r\n" . '			<integer>1</integer>'
				. "\r\n" . '		</dict>'
				. "\r\n";
		}

		return $result;
	}
}
