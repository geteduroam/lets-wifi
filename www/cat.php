<!DOCTYPE html>
<!--
-----BEGIN LETSWIFI BLOCK-----
<?php
$baseUrl = ( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https' )
	. '://'
	. $_SERVER['HTTP_HOST'];
echo implode("\n", str_split(base64_encode(json_encode([
	'authorization_endpoint' => $baseUrl . '/authorize.php',
	'token_endpoint' => $baseUrl . '/token.php',
	'generator_endpoint' => $baseUrl . '/generator.php',
])), 64));
?>

-----END LETSWIFI BLOCK-----
-->
<html lang="en">
<title>Let's Wifi</title>

<h1>Let's Wifi</h1>
<p>This page is used by the eduroam app. A download link should be added here eventually.
