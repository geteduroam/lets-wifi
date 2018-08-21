<?php require implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'vendor', 'autoload.php']);

use Uninett\LetsWifi\LetsWifiApp;

session_start();
header('Content-Type: text/plain');
$provider = LetsWifiApp::getInstance()->getOAuthProvider();
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
		$redirect = '/';
		if ( isset( $_SESSION['redirect'] ) ) {
			$redirect = $_SESSION['redirect'];
			unset($_SESSION['redirect']);
		}
		header('Location: ' . $redirect);
	} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
		// Failed to get the access token or user details.
		exit($e->getMessage());
	}
}
