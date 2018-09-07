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

setenv("DATABASE_TYPE", "redis");
setenv("DATABASE_URI", getenv('REDISCLOUD_URL'));


setenv("INDEX_TYPE", "neo4j");
setenv("INDEX_URI", getenv('GRAPHENEDB_URL'));

preg_match("/^https:\/\/(cloud-cube((-eu)?)).+$/", getenv('CLOUDCUBE_URL'), $matches); // // https://cloud-cube.s3.amazonaws.com
setenv("STORAGE_TYPE", "s3");
setenv("STORAGE_URI", json_encode(
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

setenv("MAILGUN_KEY", getenv("MAILGUN_API_KEY"));

setenv("STREAM_KEY", parse_url(getenv("STREAM_URL"), PHP_URL_USER));
setenv("STREAM_SECRET", parse_url(getenv("STREAM_URL"), PHP_URL_PASS));