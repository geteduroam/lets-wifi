<?php require implode( DIRECTORY_SEPARATOR, [dirname( __DIR__ ), 'vendor', 'autoload.php'] );

use Uninett\LetsWifi\Authentication\EapTlsMethod;
use Uninett\LetsWifi\X509\CA;
use Uninett\LetsWifi\X509\CSR;
use Uninett\LetsWifi\X509\DN;
use Uninett\LetsWifi\X509\KeyConfig;
use Uninett\LetsWifi\X509\PrivateKey;
use Uninett\LetsWifi\Generator\ProfileMetadata;
use Uninett\LetsWifi\Generator\Apple\AppleMobileConfigGenerator;
use Uninett\LetsWifi\Generator\EapConfig\EapConfigGenerator;
use Uninett\LetsWifi\Generator\PKCS12\PKCS12ConfigGenerator;
use Uninett\LetsWifi\Generator\Pem\PemConfigGenerator;
use Uninett\LetsWifi\Generator\Windows\WindowsConfigGenerator;

use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Rules\{
	ForAudience,
	IssuedBy,
	NotExpired
};

use Uninett\LetsWifi\LetsWifiApp;

// Quick 'n dirty proof of concept

$app = LetsWifiApp::getInstance();

if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
	$authorization = $_SERVER['HTTP_AUTHORIZATION'];
	if ( substr( $authorization, 0, 7 ) !== 'Bearer ' ) {
		header('Content-Type: text/plain', true, 422);
		die("422 Forbidden\r\n\r\nIllegal authorization header\r\n");
	}
	$tokenString = substr( $authorization, 7 );

	$sharedKey = $app->getSymmetricKey();

	$parser = ( new Parser() )
		->setKey( $sharedKey )
		->addRule( new NotExpired )
		->addRule( new IssuedBy( $app->getIssuerPrincipal() ) )
		->addRule( new ForAudience( $app->getGeneratorPrincipal() ) )
		->setPurpose( Purpose::local() )
		->setAllowedVersions( ProtocolCollection::v2() );

	try {
		$token = $parser->parse( $tokenString );
	} catch ( PasetoException $ex ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nCannot process token\r\n" );
	}

	if ( !in_array( $_REQUEST['format'], explode( ' ', $token->get( 'scope' ) ) ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal format specified\r\n" );
	}

	$user = $token->getSubject();
	$format = $_REQUEST['format'];
	$password = uniqid();
	$days = 365;
} elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	session_start();
	if ( $_POST['user'] !== $_SESSION['oauth_user'] ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nIllegal user specified\r\n" );
	}

	if ( !in_array($_POST['format'], ['mobileconfig', 'eap-metadata', 'pkcs12', 'pem', 'windows']) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal format specified\r\n" );
	}

	$user = $_POST['user'];
	$format = $_POST['format'];
	$password = empty($_POST['password']) ? uniqid() : $_POST['password'];
	$days = empty($_POST['days']) ? 365 : $_POST['days'];
} else {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nRequest must be POST and/or have a bearer token\r\n" );
}

if ( ((int)$days) != $days ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nDays must be integer number\r\n" );
}
if ( $days < 1 || $days > 365 ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nInvalid amount of validity days\r\n" );
}

try {
	if ( preg_match( '/^[a-z0-9]$/', $_POST['user'] ) ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nIllegal user specified\r\n" );
	}

	$dn = new DN( $app->getCertificateSubjectAttributes() +
			[
				'commonName' => $user . '@' . $app->getRealm(),
			]
		);

	$privkey = PrivateKey::generate( new KeyConfig(
			[
				'private_key_bits' => 2048,
				'private_key_type' => OPENSSL_KEYTYPE_RSA,
			]
		) );
	$csrConfigArgs = new KeyConfig( ['digest_alg' => 'sha256', 'x509_extensions' => 'client_req'] );
	$csr = new CSR( $dn, $privkey, $csrConfigArgs );

	$ca = new CA(
			implode( DIRECTORY_SEPARATOR, ['..', 'data', 'ca'] ),
			'ca.pem',
			'ca.key',
			'whatever'
		);
	$x509 = $ca->sign(
			$csr,
			$days,
			$csrConfigArgs
		);

	$p12Out = $x509->exportPKCS12WithPrivateKey( $password );

	if ( $format === 'mobileconfig' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.mobileconfig"' );
		$generator = new AppleMobileConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@' . $app->getRealm(), // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							$password
						),
				]
			);
	} elseif ( $format === 'eap-metadata' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.xml"' );
		$generator = new EapConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@' . $app->getRealm(), // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							$password
						),
				]
			);
	} elseif ( $format === 'pkcs12' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.p12"' );
		$generator = new PKCS12ConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@' . $app->getRealm(), // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							$password
						),
				]
			);
	} elseif ( $format === 'pem' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.pem"' );
		$generator = new PemConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@' . $app->getRealm(), // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							$password
						),
				]
			);
	} elseif ( $format === 'windows' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.exe"' );
		$generator = new WindowsConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@' . $app->getRealm(), // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							$password
						),
				]
			);
	} else {
		throw new \DomainException("Unknown format: $format");
	}

	$output = $generator->__toString();
	header( 'Content-Type: ' . $generator->getContentType() );
	echo $output;
} catch ( \Exception $e ) {
	header( 'Content-Type: text/plain', true, 500 );
	throw $e;
}
