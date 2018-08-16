<?php require implode( DIRECTORY_SEPARATOR, [dirname( __DIR__ ), 'vendor', 'autoload.php'] );

use Uninett\LetsWifi\Authentication\EapTlsMethod;
use Uninett\LetsWifi\X509\CA;
use Uninett\LetsWifi\X509\CSR;
use Uninett\LetsWifi\X509\DN;
use Uninett\LetsWifi\X509\KeyConfig;
use Uninett\LetsWifi\X509\PrivateKey;
use Uninett\LetsWifi\Generator\ProfileMetadata;
use Uninett\LetsWifi\Generator\EapConfig\EapConfigGenerator;
use Uninett\LetsWifi\Generator\Apple\AppleMobileConfigGenerator;
use Uninett\LetsWifi\Generator\PKCS12\PKCS12ConfigGenerator;

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

// Quick 'n dirty proof of concept

if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
	$authorization = $_SERVER['HTTP_AUTHORIZATION'];
	if ( substr( $authorization, 0, 7 ) !== 'Bearer ' ) {
		header('Content-Type: text/plain', true, 422);
		die("422 Forbidden\r\n\r\nIllegal authorization header\r\n");
	}
	$tokenString = substr( $authorization, 7 );

	$sharedKey = new SymmetricKey( 'YENuGQd3avOLdM8UBxPhRZRxhmQxXR5g' ); // TODO: Give random BITS!

	$parser = ( new Parser() )
		->setKey( $sharedKey )
		->addRule( new NotExpired )
		->addRule( new IssuedBy( 'lets-wifi-token' ) )
		->addRule( new ForAudience( 'lets-wifi-generator' ) )
		->setPurpose( Purpose::local() )
		->setAllowedVersions( ProtocolCollection::v2() );

	try {
		$token = $parser->parse( $tokenString );
	} catch ( PasetoException $ex ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nCannot process token\r\n\r\n" );
	}

	if ( !in_array( $_REQUEST['format'], explode( ' ', $token->get( 'scope' ) ) ) ) {
		header( 'Content-Type: text/plain', true, 400 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal format specified\r\n" );
	}

	$user = $token->getSubject();
} elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	session_start();
	if ( $_POST['user'] !== $_SESSION['oauth_user'] ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nIllegal user specified\r\n" );
	}

	if ( !in_array($_POST['format'], ['mobileconfig', 'eap-metadata', 'pkcs12']) ) {
		header( 'Content-Type: text/plain', true, 400 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal format specified\r\n" );
	}

	$user = $_POST['user'];
} else {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nRequest must be POST and/or have a bearer token\r\n" );
}

try {
	if ( preg_match( '/^[a-z0-9]$/', $_POST['user'] ) ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nIllegal user specified\r\n" );
	}

	$dn = new DN(
			[
				'countryName' => 'NO',
				'localityName' => 'Trondheim',
				'organizationName' => 'UNINETT AS',
				'commonName' => $user . '@demo.eduroam.no',
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
			30, /* days */
			$csrConfigArgs
		);

	$p12Out = $x509->exportPKCS12WithPrivateKey( 'password' );

	if ( $format === 'mobileconfig' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.mobileconfig"' );
		$generator = new AppleMobileConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@demo.eduroam.no', // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							'password'
						),
				]
			);
	} elseif ( $format === 'eap-metadata' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.xml"' );
		$generator = new EapConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@demo.eduroam.no', // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							'password'
						),
				]
			);
	} elseif ( $format === 'pkcs12' ) {
		header( 'Content-Disposition: attachment; filename="'. $user .'.p12"' );
		$generator = new PKCS12ConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$user . '@demo.eduroam.no', // outer identity
							[$ca], // CA for server certificate
							$p12Out, // user credential
							'password'
						),
				]
			);
	}

	$output = $generator->__toString();
	header( 'Content-Type: ' . $generator->getContentType() );
	echo $output;
} catch ( \Exception $e ) {
	header( 'Content-Type: text/plain', true, 500 );
	throw $e;
}
