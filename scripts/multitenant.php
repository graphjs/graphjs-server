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
 * This class contains functions needed to generate a 
 * new GraphJS tenant on a typical:
 * * Ubuntu 16.04 Linux OS
 * * PHP 7.1
 * * Nginx (as a proxy server)
 * * docker with redis and neo4j images 
 * system.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
 final class MT {

     private $root;

     public function __construct() {
         $this->root = dirname(__DIR__ . "/../envs");
     }

     public function findConfigNum() {
         $list = array_diff(scandir($this->root), array(".", ".."));
         rsort($list, SORT_NUMERIC);
         return (int) $list[0];
     }
 
  public static function makeEnvFile() {
  
  }
  
  public static function setupNginxConf() {
  
  }
  
  public static function reloadServers() {
  
  }
  
  public static function setupSupervisorConf() {
  
  }
 
 }
