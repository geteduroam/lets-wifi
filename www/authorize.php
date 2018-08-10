<?php
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
$sharedKey = new SymmetricKey( 'YENuGQd3avOLdM8UBxPhRZRxhmQxXR5g' );
$user = 'user';

header( 'Cache-Control: no-store' );
header( 'Pragma: no-cache' );

if ( array_reduce( ['response_type', 'code_challenge_method', 'scope', 'code_challenge', 'redirect_uri', 'client_id', 'state'], function( $carry, $item ){
	return $carry && isset( $_GET[$item] );
}, true ) ) {
	if ( $_GET['response_type'] !== 'code' ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nOnly code is supported as response_type\r\n\r\n" );
	}
	if ( $_GET['code_challenge_method'] !== 'S256' ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nOnly S256 is supported as code_challenge_method\r\n\r\n" );
	}
	if ( $_GET['scope'] !== 'eap-metadata' ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nRequested scope not allowed\r\n\r\n" );
	}
	if ( !preg_match( '/^[a-zA-Z0-9_\\-]{43}$/', $_GET['code_challenge'] ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal code challenge for S256\r\n\r\n" );
	}
	if ( $_GET['redirect_uri'] !== 'http://[::1]:1080/authorize.php' ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nRequested scope not allowed\r\n\r\n" );
	}
	if ( $_GET['client_id'] !== '00000000-0000-0000-0000-000000000000' ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nUnknown client ID\r\n\r\n" );
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
				'iss' => 'lets-wifi',
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
			echo $_GET['redirect_uri'] . '?' . http_build_query(
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
</form><?php
	exit;
} elseif ( array_reduce( ['grant_type', 'code', 'redirect_uri', 'client_id', 'code_verifier'], function( $carry, $item ){
	return $carry && isset( $_GET[$item] );
}, true ) ) {
	if ( $_GET['redirect_uri'] !== 'http://[::1]:1080/authorize.php' ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nRequested scope not allowed\r\n\r\n" );
	}
	if ( !preg_match( '/^[a-zA-Z0-9_\\-]{43}$/', $_GET['code_verifier'] ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nIllegal code verifier for S256\r\n\r\n" );
	}

	$parser = ( new Parser() )
		->setKey( $sharedKey )
		->addRule( new NotExpired )
		->addRule( new IssuedBy( 'lets-wifi' ) )
		->addRule( new ForAudience( 'lets-wifi-issuer' ) )
		->setPurpose( Purpose::local() )
		->setAllowedVersions( ProtocolCollection::v2() );

	try {
		$token = $parser->parse( $_GET['code'] );
	} catch ( PasetoException $ex ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nCannot process token\r\n\r\n" );
	}
	header( 'Content-Type: text/plain', true, 500 );
	if ($token->get( 'code_challenge_method' ) !== 'S256') {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nToken has no valid code_challenge_method\r\n\r\n" );
	}
	// For this PoC we won't be using constant time
	$challengeB64 = $token->get( 'code_challenge' );
	$verifierB64 = $_GET['code_verifier'];
	$challengeBin = base64_decode( strtr( $challengeB64, '_-', '/+' ) );
	$verifierBin = base64_decode( strtr( $verifierB64, '_-', '/+' ) );
	$verifiedBin = hash( 'sha256', $verifierB64, true );

	if ( $verifiedBin !== $challengeBin ) {
		header( 'Content-Type: text/plain', true, 403 );
		die( "403 Forbidden\r\n\r\nAccess token does not match\r\n\r\n" );
	}

	header( 'Content-Type: application/json;charset=UTF-8', true );

	$newToken = ( new Builder() )
		->setKey( $sharedKey )
		->setVersion( new Version2() )
		->setPurpose( Purpose::local() )
		->setExpiration(
			( new DateTime() )->add( new DateInterval( 'PT1H' ) )
		)
		->setClaims( [
			'iss' => 'lets-wifi',
			'aud' => 'lets-wifi',
			'sub' => $token->getSubject(),
		] );

	echo json_encode(
			[
				'access_token' => $newToken->toString(),
				'token_type' => 'Bearer',
				'expires_in' => 3600,
			]
		, JSON_PRETTY_PRINT );
	exit;
}
header( 'Content-Type: text/plain', true, 422 );
die( "422 Unprocessable Entity\r\n\r\nMissing GET parameter\r\n\r\n" );
