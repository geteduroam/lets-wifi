<?php require implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'vendor', 'autoload.php']);

$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
session_start();
if (!isset($_SESSION['oauth_user'])) {
	$_SESSION['redirect'] = $baseUrl . $_SERVER['REQUEST_URI'];
	header('Location: /oauth.php');
	exit;
}

use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Protocol\Version2;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Rules\{
	ForAudience,
	IssuedBy,
	NotExpired
};

use Uninett\LetsWifi\LetsWifiApp;

// Very very proof of concept, NO NOT USE IN PRODUCTION

// http://localhost:1080/authorize.php?response_type=code&code_challenge_method=S256&scope=eap-metadata&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM&redirect_uri=http://localhost:1080/authorize.php&client_id=00000000-0000-0000-0000-000000000000&state=0

// Settings
$sharedKey = LetsWifiApp::getInstance()->getSymmetricKey();
$user = $_SESSION['oauth_user'];
$clients = LetsWifiApp::getInstance()->getClients();

header( 'Cache-Control: no-store' );
header( 'Pragma: no-cache' );

foreach( ['response_type', 'code_challenge_method', 'scope', 'code_challenge', 'redirect_uri', 'client_id', 'state'] as $key ) {
	if ( !isset($_GET[$key] ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nMissing GET parameter '$key'\r\n" );
	}
}

if ( $_GET['response_type'] !== 'code' ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nOnly code is supported as response_type\r\n" );
}
if ( $_GET['code_challenge_method'] !== 'S256' ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nOnly S256 is supported as code_challenge_method\r\n" );
}
if ( !preg_match( '/^[a-zA-Z0-9_\\-]{43}$/', $_GET['code_challenge'] ) ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nIllegal code challenge for S256\r\n" );
}
if ( !array_key_exists( $_GET['client_id'], $clients ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nUnknown client ID\r\n\r\n" );
}
if ( !in_array( $_GET['redirect_uri'], $clients[$_GET['client_id']]['redirect'], true ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nRequested redirect URI not allowed\r\n" );
}
if ( !in_array( $_GET['scope'], $clients[$_GET['client_id']]['scope'], true ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nRequested scope not allowed\r\n" );
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( isset( $_POST['approve'] ) && $_POST['approve'] === '1' ) {
		$token = ( new Builder() )
			->setKey( $sharedKey )
			->setVersion( new Version2() )
			->setPurpose( Purpose::local() )
			->setExpiration(
				( new DateTime() )->add( LetsWifiApp::getInstance()->getAuthTokenValidity() )
			)
			->setClaims( [
				'iss' => LetsWifiApp::getInstance()->getAuthPrincipal(),
				'aud' => LetsWifiApp::getInstance()->getIssuerPrincipal(),
				'sub' => $user,
				'scope' => $_GET['scope'],
				'code_challenge_method' => $_GET['code_challenge_method'],
				'code_challenge' => $_GET['code_challenge'],
			] );

		$url = $_GET['redirect_uri'] . '?' . http_build_query(
				[
					'code' => $token->toString(),
					'state' => $_GET['state'],
				]
			);
	} else {
		$url = $_GET['redirect_uri'] . '?' . http_build_query(
				[
					'error' => 'access_denied',
					'state' => $_GET['state'],
				]
			);
	}
	header( 'Content-Type: text/plain' );
	header( "Location: $url", true, 302 );
	die( "$url\r\n" );
}
?><!DOCTYPE html>
<html lang="en">
<title>Authorize eduroam client</title>
<style type="text/css">
button {
	cursor: pointer;
}
</style>

<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<link href="assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/local/jumbotron-narrow.css" rel="stylesheet">

<div class="container">
	<div class="header clearfix">
		<h3 class="text-muted">eduroam</h3>
	</div>

	<div class="jumbotron">
		<h1>Authorize client</h1>
		<p>About to create an eduroam profile</p>

		<form method="post">
			<p>
				<button type="submit" name="approve" value="0" class="btn btn-danger">Reject</button>
				<button type="submit" name="approve" value="1" class="btn btn-success">Approve</button>
			</p>
		</form>
	</div>

	<footer class="footer">
		<p>Uninett AS</p>
	</footer>
</div>
