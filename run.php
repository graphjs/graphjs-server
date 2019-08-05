#!/usr/bin/php
<?php
@ob_end_clean();
ob_start();
use Garden\Cli\Cli;
$composerClassLoader = require "vendor/autoload.php";
define('APP_ROOT', __DIR__);
$cli = new Cli();
$cli->description('GraphJS Server')
    ->opt('conf:C', 'Config file.', false)
    ->opt('port:P', 'Port number to serve requests.', false, 'integer')
    ->opt('domain:D', 'CORS domain(s) to serve. Separate by semicolon, if multiple.', false)
    ->opt('heroku:H', 'Set "yes" if this is a Heroku installation', false);
$args = $cli->parse($argv, true);
$port = $args->getOpt(
    'port', 
    (getenv('PORT') ?  getenv('PORT') : 1338) // default
);
$configs = $args->getOpt('conf', "");
$cors = $args->getOpt('domain', "");
$heroku = ($args->getOpt('heroku', false) === "yes");
$server = new \GraphJS\Daemon($configs, $cors, $heroku);
$server->setPort($port); 
ob_end_flush();
error_log(sprintf("Serving through port %s with the config file %s", (string) $port, $configs));
$server->serve();
