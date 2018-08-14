<?php
require implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'vendor', 'autoload.php']);
session_start();
header('Content-Type: text/plain');
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
	'clientId'                => '00000000-0000-0000-0000-000000000000',    // The client ID assigned to you by the provider
	'clientSecret'            => '00000000-0000-0000-0000-000000000000',   // The client password assigned to you by the provider
	'redirectUri'             => 'https://demo.eduroam.no/oauth.php',
	'urlAuthorize'            => 'https://auth.dataporten.no/oauth/authorization',
	'urlAccessToken'          => 'https://auth.dataporten.no/oauth/token',
	'urlResourceOwnerDetails' => 'https://auth.dataporten.no/userinfo'
]);
if (!isset($_GET['code'])) {
	$authorizationUrl = $provider->getAuthorizationUrl();
	$_SESSION['oauth2state'] = $provider->getState();
	header('Location: ' . $authorizationUrl);
	exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);
	exit('Invalid state');
} else {
	try {
		$accessToken = $provider->getAccessToken('authorization_code', [
			'code' => $_GET['code']
		]);
		$resourceOwner = $provider->getResourceOwner($accessToken);
		$_SESSION['oauth_user'] = $resourceOwner->toArray()['user']['userid'];
		header('Location: /');
	} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
		// Failed to get the access token or user details.
		exit($e->getMessage());
	}
}
