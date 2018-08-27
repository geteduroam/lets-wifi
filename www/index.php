<?php
$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
session_start();
if (!isset($_SESSION['oauth_user'])) {
	$_SESSION['redirect'] = $baseUrl . $_SERVER['REQUEST_URI'];
}
?><!DOCTYPE html>
<!--
-----BEGIN LETSWIFI BLOCK-----
<?php
$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
echo implode("\n", str_split(base64_encode(json_encode([
	'authorization_endpoint' => $baseUrl . '/authorize.php',
	'token_endpoint' => $baseUrl . '/token.php',
	'generator_endpoint' => $baseUrl . '/generate.php',
])), 64));
?>

-----END LETSWIFI BLOCK-----
-->
<html lang="en">
<title>Connect to eduroam</title>
<style type="text/css">
.formats input {
	display: none;
}
.formats img {
	width: 3em;
	height: 3em;
	margin: .5em;
	background: rgba(0, 0, 0, .1);
	padding: .5em;
	border-radius: .2em;
	border: 3px solid rgba(0,0,0,0);
	cursor: pointer;
}
.formats input:checked ~ label img {
	border: 3px solid #0022aa;
}
#format-advanced {
	width: 14em;
	text-align: left;
	margin: auto;
	margin-bottom: 2em;
}
#format-advanced li {
	list-style: none;
}
#format-advanced input {
	margin-right: .7em;
}
</style>

<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<link href="assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/local/jumbotron-narrow.css" rel="stylesheet">

<div class="container">
	<div class="header clearfix">
		<nav>
			<ul class="nav nav-pills pull-right">
				<li role="presentation" class="active"><a>Connect</a></li>
				<li role="presentation"><a href="about.php">About</a></li>
<?php if (isset($_SESSION['oauth_user'])): ?>
				<li role="presentation"><a href="logout.php">Logout</a></li>
<?php endif; ?>
			</ul>
		</nav>
		<h3 class="text-muted">eduroam</h3>
	</div>

<?php if (isset($_SESSION['oauth_user'])): ?>
	<form method="post" action="generate.php">
		<div class="jumbotron">
			<h1>New account</h1>
			<p class="lead">
				Select how long this account must be valid:<br>
				<input type="number" name="days" value="365" min="1" max="365" style="width:4em"> days.
			</p>
			<p class="formats">
				<span>
					<input type="radio" name="format" id="format-windows" value="windows" checked>
					<label for="format-windows"><img src="img/windows.svg" alt="Windows" width="32" height="32"></label>
				</span>
				<span>
					<input type="radio" name="format" id="format-mobileconfig" value="mobileconfig">
					<label for="format-mobileconfig"><img src="img/apple.svg" alt="Apple" width="32" height="32"></label>
				</span>
				<span>
					<img src="img/advanced.svg" onclick="$('#format-advanced').show()" width="32" height="32">
				</span>
			</p>
			<div id="format-advanced">
				<p>Advanced format</p>
				<ul>
					<li>
						<input type="radio" name="format" id="format-eap-metadata" value="eap-metadata">
						<label for="format-eap-metadata">eap-metadata</label>
					</li>

					<li>
						<input type="radio" name="format" id="format-pkcs12" value="pkcs12">
						<label for="format-pkcs12">pkcs12</label>
					</li>

					<li>
						<input type="radio" name="format" id="format-pem" value="pem">
						<label for="format-pem">pem</label>
					</li>
				</ul>
			</div>
			<p>
				<input type="hidden" name="user" value="<?= $_SESSION['oauth_user'] ?>">
				<button class="btn btn-lg btn-success" id="submit" type="submit" data-toggle="modal" data-target="">
					Get eduroam
				</button>
			</p>
			<hr>
			<p><small>Alternatively, <a href="app/EduroamApp.exe">download the generic Windows app</a></small></p>
		</div>

		<div class="modal fade" id="password-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Profile password</h4>
					</div>
					<div class="modal-body text-center">
						<p>When you install this profile, you will be asked for a password.
						<p>Pick a password here:</p>
						<p><input type="text" name="password" value="password" style="font-size:2.5em;border:none;text-align:center;display:block;width:100%">
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary btn-lg">Got it!</button>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php else: ?>

	<div class="jumbotron">
		<h1>Log in</h1>
		<p class="lead">
			Log in with an edugain account to get access to eduroam
		</p>
		<p>
			<a href="oauth.php" class="btn btn-lg btn-success">
				Log in
			</a>
		</p>
		<hr>
		<p><small>Alternatively, <a href="app/EduroamApp.exe">download the generic Windows app</a></small></p>
	</div>

<?php endif; ?>

	<footer class="footer">
		<p>Uninett AS</p>
	</footer>
</div>

<script src="assets/jquery/jquery.min.js"></script>
<script src="assets/bootstrap/dist/js/bootstrap.min.js"></script>
<script>
	$('#format-advanced').hide();
	$('#format-windows, #format-mobileconfig').click(function(){
		$('#format-advanced').hide();
	});
	$('.format input, #format-advanced input').click(function(){
		$('#submit').attr('type', 'submit');
		$('#submit').attr('data-target', '');
	});
	$('#format-pkcs12').click(function(){
		$('#submit').attr('type', 'button');
		$('#submit').attr('data-target', '#password-modal');
	});
</script>
