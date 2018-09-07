#!/usr/bin/php
<?php
@ob_end_clean();
ob_start();
use Garden\Cli\Cli;
require "vendor/autoload.php";
$cli = new Cli();
$cli->description('GraphJS Server')
    ->opt('conf:C', 'Config file.', false)
    ->opt('port:P', 'Port number to serve requests.', false, 'integer')
    ->opt('domain:D', 'CORS domain(s) to serve. Separate by semicolon, if multiple.', false);
$args = $cli->parse($argv, true);
$port = $args->getOpt(
    'port', 
    (getenv('PORT') ?  getenv('PORT') : 1338) // default
);
$configs = $args->getOpt('conf', "");
$cors = $args->getOpt('domain', "http://localhost:8080");
$server = new \GraphJS\Daemon($configs, $cors);
$server->setPort($port); 
ob_end_flush();
error_log(sprintf("Serving through port %s with the config file %s", (string) $port, $configs));
$server->serve();
