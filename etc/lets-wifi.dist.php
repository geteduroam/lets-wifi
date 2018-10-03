<?php
return [
	'caDirectory'                  => implode( DIRECTORY_SEPARATOR, [ dirname( __DIR__ ), 'var', 'geteduroam-ca' ] ),
	'caPassword'                   => null,

	'symmetricKey'                 => hex2bin('64 character HEX string'),
	'authPrincipal'                => 'lets-wifi-auth',
	'issuerPrincipal'              => 'lets-wifi-issuer',
	'generatorPrincipal'           => 'lets-wifi-generator',
	'authTokenValidity'            => 'PT10M',
	'idTokenValidity'              => 'PT1H',
	'serverAdministratorUsers'     => ['admin'],
	'realm'                        => 'example.com',
	'clients' =>
		[
			'f817fbcc-e8f4-459e-af75-0822d86ff47a' =>
				[
					'redirect' => ['http://localhost:8080/'],
					'scope'    => ['eap-metadata']
				],
		],
	'certificateSubjectAttributes' =>
		[
			'countryName'      => 'NO',
			'localityName'     => 'Trondheim',
			'organizationName' => 'Uninett AS',
		],
	'oauthProvider' =>
		[
			'clientId'                => '00000000-0000-0000-0000-000000000000',
			'clientSecret'            => '00000000-0000-0000-0000-000000000000',
			'redirectUri'             => 'http://localhost:1080/oauth.php',
			'urlAuthorize'            => 'https://auth.dataporten.no/oauth/authorization',
			'urlAccessToken'          => 'https://auth.dataporten.no/oauth/token',
			'urlResourceOwnerDetails' => 'https://auth.dataporten.no/userinfo'
		],
];
