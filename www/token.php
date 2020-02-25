<?php require implode( DIRECTORY_SEPARATOR, [dirname( __DIR__ ), 'vendor', 'autoload.php'] );

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

use Uninett\LetsWifi\LetsWifiApp;

// Very very proof of concept, NO NOT USE IN PRODUCTION

// Settings
$app = LetsWifiApp::getInstance();
$sharedKey = $app->getSymmetricKey();
$clients = $app->getClients();

$baseUrl = ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];

header( 'Cache-Control: no-store' );
header( 'Pragma: no-cache' );

// @TODO require redirect_uri
// @TODO do not support GET
$required = ['grant_type', 'code', 'redirect_uri', 'client_id', 'code_verifier'];
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	// @TODO remove this line
	$required = ['grant_type', 'code', 'client_id', 'code_verifier'];

	// @TODO activate these lines
	// header( 'Content-Type: text/plain', true, 400 );
	// die( "400 Bad Request\r\n\r\nToken must be obtained through POST request\r\n" );
}
// @TODO inline $required as soon the conditionals are gone
foreach( $required as $key ) {
	// @TODO remove $_GET check, only accept $_POST
	if ( !isset( $_GET[$key] ) && !isset( $_POST[$key] ) ) {
		header( 'Content-Type: text/plain', true, 400 );
		die( "400 Bad Request\r\n\r\nMissing POST parameter '$key'\r\n" );
	}
	// @TODO remove this
	if ( !isset( $_POST[$key] ) ) {
		$_POST[$key] = $_GET[$key];
	}
}

if ( 'authorization_code' !== $_POST['grant_type'] ) {
	header( 'Content-Type: text/plain', true, 400 );
	die( "400 Bad Request\r\n\r\ngrant_type must be \"authorization_code\"\r\n" );
}
if ( !preg_match( '/^[a-zA-Z0-9\\-\\._~]{43,128}$/', $_POST['code_verifier'] ) ) {
	header( 'Content-Type: text/plain', true, 400 );
	die( "400 Bad Request\r\n\r\ncode_verifier must be 43-128 bytes and only contain alphanumeric and -._~\r\n" );
}

if ( !array_key_exists( $_POST['client_id'], $clients ) ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nUnknown client ID\r\n" );
}
// @TODO remove isset check when POST is enforced
if ( isset( $_POST['redirect_uri'] ) ) {
	if ( in_array( $_POST['redirect_uri'], $clients[$_POST['client_id']]['redirect'] ) ) {
		header( 'Content-Type: text/plain', true, 422 );
		die( "422 Unprocessable Entity\r\n\r\nProvided redirect_uri is not allowed for the provided client_id\r\n" );
	}
}

$parser = ( new Parser() )
	->setKey( $sharedKey )
	->addRule( new NotExpired )
	->addRule( new IssuedBy( $app->getAuthPrincipal() ) )
	->addRule( new ForAudience( $app->getIssuerPrincipal() ) )
	->setPurpose( Purpose::local() )
	->setAllowedVersions( ProtocolCollection::v2() );

try {
	$token = $parser->parse( $_POST['code'] );
} catch ( PasetoException $ex ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nCannot process token\r\n" );
}

if ($token->get( 'code_challenge_method' ) !== 'S256' ) {
	header( 'Content-Type: text/plain', true, 422 );
	die( "422 Unprocessable Entity\r\n\r\nToken has no valid code_challenge_method\r\n" );
}

// For this PoC we won't be using constant time
$challengeB64 = $token->get( 'code_challenge' );
$verifierB64 = $_POST['code_verifier'];
$challengeBin = base64_decode( strtr( $challengeB64, '_-', '/+' ) );
$verifierBin = base64_decode( strtr( $verifierB64, '_-', '/+' ) );
$verifiedBin = hash( 'sha256', $verifierB64, true );

if ( $verifiedBin !== $challengeBin ) {
	header( 'Content-Type: text/plain', true, 403 );
	die( "403 Forbidden\r\n\r\nAccess token does not match\r\n" );
}

$newToken = ( new Builder() )
	->setKey( $sharedKey )
	->setVersion( new Version2() )
	->setPurpose( Purpose::local() )
	->setExpiration(
		( new DateTime() )->add( $app->getIdTokenValidity() )
	)
	->setClaims( [
		'iss' => $app->getIssuerPrincipal(),
		'aud' => $app->getGeneratorPrincipal(),
		'sub' => $token->getSubject(),
		'scope' => $token->get( 'scope' ),
	] );

header( 'Content-Type: application/json;charset=UTF-8', true );
echo json_encode(
		[
			'access_token' => $newToken->toString(),
			'token_type' => 'Bearer',
			'expires_in' => 3600,
		]
	, JSON_PRETTY_PRINT );
exit;
