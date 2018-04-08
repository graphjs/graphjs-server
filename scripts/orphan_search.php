<?php
$orphan = $argv[1];
// 9f5ab1276e247b0aa67afe1e26abccbf
require "../vendor/autoload.php";
$c = new \Predis\Client();
$keys = $c->keys("*");
foreach($keys as $key) {
    $x = $c->get($key);
    if(strpos($x, $orphan)!==false) {
        echo $x."\n\n\n";
    }
}