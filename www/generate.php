<?php require implode( DIRECTORY_SEPARATOR, [dirname( __DIR__ ), 'src', '_autoload.php'] );

// Quick 'n dirty proof of concept

use Uninett\LetsWifi\Authentication\EapTlsMethod;
use Uninett\LetsWifi\Generator\Apple\AppleMobileConfigGenerator;
use Uninett\LetsWifi\X509\CA;
use Uninett\LetsWifi\X509\CSR;
use Uninett\LetsWifi\X509\DN;
use Uninett\LetsWifi\X509\KeyConfig;
use Uninett\LetsWifi\X509\PrivateKey;
use Uninett\LetsWifi\Generator\ProfileMetadata;

try {
	if ( $_POST['format'] !== 'mobileconfig' ) {
		header( 'Content-Type: text/plain', true, 400 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal format specified\r\n" );
	}
	if ( preg_match( '/^[a-z0-9]$/', $_POST['user'] ) ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nIllegal user specified\r\n" );
	}

	$dn = new DN(
			[
				'countryName' => 'NO',
				//'stateOrProvinceName' => '',
				'localityName' => 'Trondheim',
				'organizationName' => 'UNINETT AS',
				'commonName' => $_POST['user'] . '@demo.eduroam.no',
				'emailAddress' => 'eduroam@uninett.no'
			]
		);

	$privkey = PrivateKey::generate( new KeyConfig(
			[
				'private_key_bits' => 2048,
				'private_key_type' => OPENSSL_KEYTYPE_RSA,
			]
		) );
	$csrConfigArgs = new KeyConfig( ['digest_alg' => 'sha256'] );
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

	if ( $_POST['format'] === 'mobileconfig' ) {
		$generator = new AppleMobileConfigGenerator(
				new ProfileMetadata( 'eduroam demo', 'Demonstration of eduroam EAP-TLS generation and installation' ),
				[
					new EapTlsMethod(
							$_POST['user'] . '@demo.eduroam.no', // outer identity
							[$ca], // CA for server certificate
							$p12Out // user credential
						),
				]
			);
	}

	$output = $generator->__toString();
	header( 'Content-Type: ' . $generator->getContentType() );
	header( 'Content-Disposition: attachment; filename="'. $_POST['user'] .'.mobileconfig"' );
	echo $output;
} catch ( \Exception $e ) {
	header( 'Content-Type: text/plain', true, 500 );
	throw $e;
}