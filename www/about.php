<?php
$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
session_start();
if (!isset($_SESSION['oauth_user'])) {
	$_SESSION['redirect'] = $baseUrl . $_SERVER['REQUEST_URI'];
}
?><!DOCTYPE html>
<html lang="en">
<title>Authorize eduroam client</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<link href="assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/local/jumbotron-narrow.css" rel="stylesheet">

<div class="container">
	<div class="header clearfix">
		<nav>
			<ul class="nav nav-pills pull-right">
				<li role="presentation"><a href="./">Connect</a></li>
				<li role="presentation" class="active"><a>About</a></li>
<?php if (isset($_SESSION['oauth_user'])): ?>
				<li role="presentation"><a href="logout.php">Logout</a></li>
<?php endif; ?>
			</ul>
		</nav>
		<h3 class="text-muted">eduroam</h3>
	</div>

	<div class="jumbotron">
		<h1>geteduroam</h1>
		<p>Client certificate authentication made easy</p>
	</div>

	<p>
		This software lets you use your eduGAIN account to receive a client
		certificate, which you can use to log in to eduroam.
	</p>
	<p>
		A problem with eduroam authentication is that a client will not verify
		the authenticity of the server, unless the client is set up correctly.
		This allows a man in the middle to interscept a users' password.
	</p>
	<p>
		This problem does not occur with certificates, because the private key
		is never sent over the air.  The disadvantage of using certificates is
		that it is hard for end users to set up.
	</p>
	<p>
		Using this web application, a user can easily generate certificates in
		different formats to be used on their operating system.  Apple users
		will receive a .mobileconfig file, which can be read natively by any
		supported operating system by Apple.  Windows does not have native
		support for wifi configuration through a configuration profile, so
		Windows users can download an .exe file that will take care of all
		configuration for them.
	</p>
	<p>
		For other operating systems, download a pem file or a pkcs12 container,
		which you can then configure manually for your operating system.  This
		is known to work for UNIX-like systems with wpa_supplicant (FreeBSD,
		Linux, NetBSD) as well as some other platforms (OpenWRT).  Support for
		more systems may be added later.
	</p>
	<p>
		Finally, we support retrieval of certificates through OIDC, allowing for
		future third party apps to provide this service in a user-friendly
		manner.
	</p>

	<footer class="footer">
		<p>Uninett AS</p>
	</footer>
</div>
