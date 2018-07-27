dev:
	mkdir -p data/ca data/server
	test -s data/ca/ca.cnf || cp openssl.cnf data/ca/ca.cnf
	test -s data/server/server.cnf || cp openssl.cnf data/server/server.cnf
	test -s data/ca/index.txt || touch data/ca/index.txt
	test -s data/ca/serial || echo 01 >data/ca/serial
	test -s data/ca/ca.key -a -s data/ca/ca.pem || openssl req -new -x509 -keyout data/ca/ca.key -out data/ca/ca.pem -days 60 -config data/ca/ca.cnf
	test -s data/server/server.csr -a -s data/server/server.key || openssl req -new  -out data/server/server.csr -keyout data/server/server.key -config data/server/server.cnf
	test -s data/server/server.crt || { cd data/ca/ ; openssl ca -batch -keyfile ca.key -cert ca.pem -in ../server/server.csr -key whatever -out ../server/server.crt -config ../server/server.cnf; }
	php -S [::1]:1080 -t www/

composer.phar:
	curl -sSLO https://getcomposer.org/composer.phar || wget https://getcomposer.org/composer.phar

php-cs-fixer-v2.phar:
	curl -sSLO https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar || wget https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar

phpDocumentor.phar:
	curl -sSLO http://phpdoc.org/phpDocumentor.phar || wget http://phpdoc.org/phpDocumentor.phar

psalm.phar:
	curl -sSLO https://github.com/vimeo/psalm/releases/download/2.0.8/psalm.phar || wget https://github.com/vimeo/psalm/releases/download/2.0.8/psalm.phar

vendor: composer.phar
	php composer.phar install

psalm: psalm.phar vendor
	php psalm.phar

codestyle: php-cs-fixer-v2.phar
	php php-cs-fixer-v2.phar fix

phpdoc: phpDocumentor.phar
	php phpDocumentor.phar -d src/ -t phpdoc/

camera-ready: codestyle psalm

clean:
	rm -rf composer.phar php-cs-fixer-v2.phar phpDocumentor.phar psalm.phar vendor phpdoc dev

.PHONY: camera-ready codestyle psalm phpcs clean dev
