<?php
$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
session_start();
if (!isset($_SESSION['oauth_user'])) {
	$_SESSION['redirect'] = $baseUrl . '/authorize.php';
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

require implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'vendor', 'autoload.php']);

// Very very proof of concept, NO NOT USE IN PRODUCTION

// http://[::1]:1080/authorize.php?response_type=code&code_challenge_method=S256&scope=eap-metadata&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM&redirect_uri=http://[::1]:1080/authorize.php&client_id=00000000-0000-0000-0000-000000000000&state=0

// Settings
$sharedKey = new SymmetricKey( 'YENuGQd3avOLdM8UBxPhRZRxhmQxXR5g' ); // TODO: Give random BITS!
$user = 'user'; // TODO: Get some login system
$clients = [
	'00000000-0000-0000-0000-000000000000' => [
		'redirect' => ['http://localhost:1080/authorize.php'],
		'scope' => ['eap-metadata']
	],
];

header( 'Cache-Control: no-store' );
header( 'Pragma: no-cache' );

foreach( ['response_type', 'code_challenge_method', 'scope', 'code_challenge', 'redirect_uri', 'client_id', 'state'] as $key ) {
	if ( !isset($_GET[$key] ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nMissing GET parameter '$key'\r\n\r\n" );
	}
}

if ( $_GET['response_type'] !== 'code' ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nOnly code is supported as response_type\r\n\r\n" );
}
if ( $_GET['code_challenge_method'] !== 'S256' ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nOnly S256 is supported as code_challenge_method\r\n\r\n" );
}
if ( !preg_match( '/^[a-zA-Z0-9_\\-]{43}$/', $_GET['code_challenge'] ) ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nIllegal code challenge for S256\r\n\r\n" );
}
if ( !array_key_exists( $_GET['client_id'], $clients ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nUnknown client ID\r\n\r\n" );
}
if ( !in_array( $_GET['redirect_uri'], $clients[$_GET['client_id']]['redirect'], true ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nRequested redirect URI not allowed\r\n\r\n" );
}
if ( !in_array( $_GET['scope'], $clients[$_GET['client_id']]['scope'], true ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nRequested scope not allowed\r\n\r\n" );
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$token = ( new Builder() )
		->setKey( $sharedKey )
		->setVersion( new Version2() )
		->setPurpose( Purpose::local() )
		->setExpiration(
			( new DateTime() )->add( new DateInterval( 'PT10M' ) )
		)
		->setClaims( [
			'iss' => 'lets-wifi-auth',
			'aud' => 'lets-wifi-issuer',
			'sub' => $user,
			'code_challenge_method' => $_GET['code_challenge_method'],
			'code_challenge' => $_GET['code_challenge'],
		] );

	if ( isset( $_POST['approve'] ) && $_POST['approve'] === '1' ) {
		$url = $_GET['redirect_uri'] . '?' . http_build_query(
				[
					'code' => $token->toString(), 'state' => $_GET['state']
				]
			);
		header( 'Content-Type: text/plain', true, 500 );
		echo "$url\r\n\r\n";
		echo $baseUrl . '/token.php?' . http_build_query(
				[
					'grant_type' => 'authorization_code',
					'code' => $token->toString(),
					'redirect_uri' => $_GET['redirect_uri'],
					'client_id' => $_GET['client_id'],
					'code_verifier' => 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk',
				]
			);
		exit;
		header( 'Location: ' . $url);
	}
}
?><!DOCTYPE html>
<title>Authorize eduroam client</title>

<form method="post">
	<button type="submit" name="approve" value="0">Reject</button>
	<button type="submit" name="approve" value="1">Approve</button>
</form>
