<?php
ob_start();
require "vendor/autoload.php";
$server = new \GraphJS\Daemon();
$server->setPort(1338); 
$server->setAccessControlAllowOrigin("*");
ob_end_flush();
$server->serve();
