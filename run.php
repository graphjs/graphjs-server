#!/usr/bin/php
<?php
@ob_end_clean();
ob_start();
use Garden\Cli\Cli;
require "vendor/autoload.php";
$cli = new Cli();
$cli->description('GraphJS Server')
    ->opt('conf:C', 'Config file.', false)
    ->opt('port:P', 'Port number to serve requests.', false, 'integer');
$args = $cli->parse($argv, true);
$port = $args->getOpt('port', 1338);
$configs = $args->getOpt('conf', "");
$server = new \GraphJS\Daemon($configs);
$server->setPort($port); 
ob_end_flush();
error_log(sprintf("Serving through port %s with the config file %s", (string) $port, $configs));
$server->serve();
