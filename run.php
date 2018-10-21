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
    ->opt('domain:D', 'CORS domain(s) to serve. Separate by semicolon, if multiple.', false)
    ->opt('heroku:H', 'Set "yes" if this is a Heroku installation', false);
$args = $cli->parse($argv, true);
$port = $args->getOpt(
    'port', 
    (getenv('PORT') ?  getenv('PORT') : 1338) // default
);
$configs = $args->getOpt('conf', "/home/ubuntu/workspace/graphjs/graphjs-server");
$cors = $args->getOpt('domain', "http://localhost");
$heroku = ($args->getOpt('heroku', false) === "yes");
if($heroku) {
    $cors = getenv("CORS_DOMAIN");
}
$server = new \GraphJS\Daemon($configs, $cors, $heroku);
$server->setPort("8081"); 
ob_end_flush();
error_log(sprintf("Serving through port %s with the config file %s", (string) $port, $configs));
$server->serve();
