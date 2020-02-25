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

function oautherror($error_description, $error = 'invalid_request', $error_uri = null) {
	$result = ['error' => $error, 'error_description' => $error_description];
	if ( null !== $error_uri ) {
		$result['error_uri'] = $error_uri;
	}

	header( 'Content-Type: application/json', true, 400 );
	die( json_encode( $result, JSON_PRETTY_PRINT ) );
}

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
	// oautherror( 'Token must be obtained through POST request' );
}
// @TODO Remove the condition
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( !isset( $_SERVER['CONTENT_TYPE'] ) || $_SERVER['CONTENT_TYPE'] !== 'application/x-www-form-urlencoded' ) {
		oautherror( 'Invalid Content-Type provided, must be application/x-www-form-urlencoded' );
	}
}
// @TODO inline $required as soon the conditionals are gone
foreach( $required as $key ) {
	// @TODO remove $_GET check, only accept $_POST
	if ( !isset( $_GET[$key] ) && !isset( $_POST[$key] ) ) {
		oautherror( "Missing POST parameter '$key'" );
	}
	// @TODO remove this
	if ( !isset( $_POST[$key] ) ) {
		$_POST[$key] = $_GET[$key];
	}
}

if ( 'authorization_code' !== $_POST['grant_type'] ) {
	oautherror( 'grant_type must be \"authorization_code\"', 'unsupported_grant_type' );
}
if ( !preg_match( '/^[a-zA-Z0-9\\-\\._~]{43,128}$/', $_POST['code_verifier'] ) ) {
	oautherror( 'code_verifier must be 43-128 bytes and only contain alphanumeric and -._~\r\n' );
}

if ( !array_key_exists( $_POST['client_id'], $clients ) ) {
	oautherror( 'Unknown client ID', 'invalid_client' );
}
// @TODO remove isset check when POST is enforced
if ( isset( $_POST['redirect_uri'] ) ) {
	if ( !in_array( $_POST['redirect_uri'], $clients[$_POST['client_id']]['redirect'] ) ) {
		oautherror( 'Provided redirect_uri is not allowed for the provided client_id', 'invalid_client' );
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
	oautherror( 'Cannot process token' );
}

if ($token->get( 'code_challenge_method' ) !== 'S256' ) {
	oautherror( 'Token has no valid code_challenge_method' );
}

// For this PoC we won't be using constant time
$challengeB64 = $token->get( 'code_challenge' );
$verifierB64 = $_POST['code_verifier'];
$challengeBin = base64_decode( strtr( $challengeB64, '_-', '/+' ) );
$verifierBin = base64_decode( strtr( $verifierB64, '_-', '/+' ) );
$verifiedBin = hash( 'sha256', $verifierB64, true );

if ( $verifiedBin !== $challengeBin ) {
	oautherror( 'code_verifier does not solve code_challenge', 'invalid_grant' );
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
