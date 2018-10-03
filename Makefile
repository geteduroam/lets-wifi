var:
	php bin/cagen

dev: var
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
