<?php
use ParagonIE\Paseto\Exception\PasetoException;
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

require implode( DIRECTORY_SEPARATOR, [dirname( __DIR__ ), 'vendor', 'autoload.php'] );

// Very very proof of concept, NO NOT USE IN PRODUCTION

// http://[::1]:1080/authorize.php?response_type=code&code_challenge_method=S256&scope=eap-metadata&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM&redirect_uri=http://[::1]:1080/authorize.php&client_id=00000000-0000-0000-0000-000000000000&state=0

// Settings
$sharedKey = new SymmetricKey( 'YENuGQd3avOLdM8UBxPhRZRxhmQxXR5g' ); // TODO: Give random BITS!
$clients = [
	'00000000-0000-0000-0000-000000000000' => [
		'redirect' => ['http://localhost:1080/authorize.php'],
		'scope' => ['eap-metadata']
	],
];
$baseUrl = ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];

header( 'Cache-Control: no-store' );
header( 'Pragma: no-cache' );

foreach( ['grant_type', 'code', 'redirect_uri', 'client_id', 'code_verifier'] as $key ) {
	if ( !isset( $_GET[$key] ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nMissing GET parameter '$key'\r\n\r\n" );
	}
}

if ( !array_key_exists( $_GET['client_id'], $clients ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nUnknown client ID\r\n\r\n" );
}
if ( !in_array( $_GET['redirect_uri'], $clients[$_GET['client_id']]['redirect'], true ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nRequested redirect URI not allowed\r\n\r\n" );
}
if ( !preg_match( '/^[a-zA-Z0-9_\\-]{43}$/', $_GET['code_verifier'] ) ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nIllegal code verifier for S256\r\n\r\n" );
}

$parser = ( new Parser() )
	->setKey( $sharedKey )
	->addRule( new NotExpired )
	->addRule( new IssuedBy( 'lets-wifi-auth' ) )
	->addRule( new ForAudience( 'lets-wifi-issuer' ) )
	->setPurpose( Purpose::local() )
	->setAllowedVersions( ProtocolCollection::v2() );

try {
	$token = $parser->parse( $_GET['code'] );
} catch ( PasetoException $ex ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nCannot process token\r\n\r\n" );
}

if ($token->get( 'code_challenge_method' ) !== 'S256' ) {
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
		'iss' => 'lets-wifi-token',
		'aud' => 'lets-wifi-generator',
		'sub' => $token->getSubject(),
		'scope' => $token->get( 'scope' ),
	] );

echo json_encode(
		[
			'access_token' => $newToken->toString(),
			'token_type' => 'Bearer',
			'expires_in' => 3600,
		]
	, JSON_PRETTY_PRINT );
exit;
