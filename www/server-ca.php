<?php require implode( DIRECTORY_SEPARATOR, [dirname( __DIR__ ), 'src', '_autoload.php'] );

$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
session_start();
if (!isset($_SESSION['oauth_user'])) {
	$_SESSION['redirect'] = $baseUrl . $_SERVER['REQUEST_URI'];
	header('Location: /oauth.php');
	exit;
}

use Uninett\LetsWifi\X509\CA;
use Uninett\LetsWifi\X509\CSR;
use Uninett\LetsWifi\X509\DN;
use Uninett\LetsWifi\X509\KeyConfig;
use Uninett\LetsWifi\X509\PrivateKey;

// Quick 'n dirty proof of concept

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	session_start();
	if ( $_POST['user'] !== $_SESSION['oauth_user'] ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nIllegal user specified\r\n" );
	}

	$user = $_POST['user'];

	if ( !in_array( $user, ['admin'], true ) ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nYou are not allowed to receive a server certificate\r\n" );
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
					'commonName' => $_POST['commonName'],
				]
			);

		$privkey = PrivateKey::generate( new KeyConfig(
				[
					'private_key_bits' => 2048,
					'private_key_type' => OPENSSL_KEYTYPE_RSA,
				]
			) );
		$csrConfigArgs = new KeyConfig( ['digest_alg' => 'sha256', 'x509_extensions' => 'server_req'] );
		$csr = new CSR( $dn, $privkey, $csrConfigArgs );

		$ca = new CA(
				implode( DIRECTORY_SEPARATOR, ['..', 'data', 'ca'] ),
				'ca.pem',
				'ca.key',
				'whatever'
			);
		$x509 = $ca->sign(
				$csr,
				90, /* days */
				$csrConfigArgs
			);

		$privPem = $x509->getPrivateKey()->exportPEMWithoutPassword();
		$pubPem = $x509->getPEMBytes( false );
		header( 'Content-Type: text/plain', true );
		echo "# Private key\n${privPem}# Client certificate\n${pubPem}# Chain\n";
		foreach ( $x509->getChain() as $c ) {
			echo $c->getPEMBytes( false );
		}
	} catch ( \Exception $e ) {
		header( 'Content-Type: text/plain', true, 500 );
		throw $e;
	}
	exit;
}
?><!DOCTYPE html>
<html lang="en">
<title>Create server certificate</title>

<form method="post" action="server-ca.php">
	<dl>
		<dt>Username</dt>
		<dd><input type="text" name="user" value="<?= $_SESSION['oauth_user'] ?>" readonly></dd>
	</dl>
	<dl>
		<dt>Common name</dt>
		<dd><input type="text" name="commonName" value=""></dd>
	</dl>
	<p><input type="submit" value="Generate server certificate"></p>
</form>
