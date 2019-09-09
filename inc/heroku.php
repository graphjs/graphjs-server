<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** 
 * This file converts heroku environment variables 
 * so that they can work together with Pho stack in harmony.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */

putenv("DATABASE_TYPE=redis");
putenv("DATABASE_URI=".getenv('REDISCLOUD_URL'));

$installationType = getenv('INSTALLATION_TYPE');

if (in_array($installationType, [ 'groupsv2', 'graphjs' ])) {
    putenv("INDEX_TYPE=redis");
    putenv("INDEX_URI=".getenv('DATABASE_URI'));
}
else {
    putenv("INDEX_TYPE=neo4j");
    putenv("INDEX_URI=".getenv('GRAPHENEDB_URL'));
}

preg_match("/^https:\/\/(cloud-cube((-eu)?)).+$/", getenv('CLOUDCUBE_URL'), $matches); // // https://cloud-cube.s3.amazonaws.com
putenv("STORAGE_TYPE=s3");
putenv("STORAGE_URI=".json_encode(
    array(
        "client" => array(
            "credentials" => array(
                "key" => getenv("CLOUDCUBE_ACCESS_KEY_ID"),
                "secret" => getenv("CLOUDCUBE_SECRET_ACCESS_KEY")
            ),
            "region"=> empty($matches[2]) ? "us-east-1" : "eu-west-1",
            "version"=>"latest",
        ),
        "bucket" => $matches[1],
        "root" => substr(parse_url(getenv('CLOUDCUBE_URL'), PHP_URL_PATH), 1)
    )
));

putenv("MAILGUN_KEY=".getenv("MAILGUN_API_KEY"));

putenv("STREAM_KEY=".parse_url(getenv("STREAM_URL"), PHP_URL_USER));
putenv("STREAM_SECRET=".parse_url(getenv("STREAM_URL"), PHP_URL_PASS));

error_log("redis uri: ".getenv("DATABASE_URI"));
error_log("neo4j uri: ".getenv("INDEX_URI"));
error_log("s3 uri: ".getenv("STORAGE_URI"));
error_log("MAILGUN_KEY: ".getenv("MAILGUN_KEY"));
error_log("STREAM_KEY: ".getenv("STREAM_KEY"));
error_log("STREAM_SECRET: ".getenv("STREAM_SECRET"));