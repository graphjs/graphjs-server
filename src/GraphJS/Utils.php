<?php
/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphJS;

use Psr\Http\Message\ServerRequestInterface;

class Utils
{

    public static function convertLegacyRoutes(array $routes): array
    {
        $copy = $routes;
        foreach($routes as $route) {
            //$routes[] = 
            $route[1] = sprintf("/v1%s", $route[1]);
            $copy[] = $route;
        }
        return $copy;
    }

    # https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
    public static function time_elapsed_string($datetime, $full = false) {
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * Utility function to sort associative arrays by value
     * 
     * Source: https://www.texelate.co.uk/blog/sort-associative-array-by-value-and-keep-keys-with-php
     *
     * @param array $arrayToSort
     * @param string $sortKey
     * @param boolean $isAsc
     * @param boolean $keepKeys
     * @return array
     */
    public static function sortAssocArrayByValue(array $arrayToSort, string $sortKey, bool $isAsc = true, bool $keepKeys = false): array 
    {
        if ($isAsc === true) {
            $sort = SORT_ASC;
        } else {
            $sort = SORT_DESC;
        }
    
        $array 	= [];
        $data	= [];
    
        // The keys are preserved by making them strings
        foreach ($arrayToSort as $key => $value) {
            if ($keepKeys === true) {
               $k = '_' . $key;
            } else {
                $k = $key;   
            }
    
            $data[$k]	= $value;
            $array[$k] 	= $value[$sortKey];
        }
    
        // This sorts the data based on $array
        array_multisort($array, $sort, $data);
    
        // If the keys are not being kept then the work is done
        if ($keepKeys === false) {
           return $data; 
        }

        // To keep the keys the new array overwrites the old one and the numerical keys are restored
        $arrayToSort = [];
    
        foreach ($data as $key => $value) {
    
            $arrayToSort[ltrim($key, '_')] = $value;
    
        }
        return $arrayToSort;
    }

    /**
     * Expands CORS Urls
     *
     * The input is taken from the command line and expanded into 
     * an array with all HTTP scheme combinations, as well as
     * cleaned format.
     * 
     * @param string $cors
     * @return array
     */
    public static function expandCorsUrl(string $cors): array
    {
        $final = ["https://graphjs.com", "http://graphjs.com", "https://www.graphjs.com", "http://www.graphjs.com"];
        if(strpos($cors, ";")===false) {
            $urls = [0=>$cors];
        }
        else {
            $urls = explode(";",$cors);
        }
        foreach($urls as $url) {
            $parsed = parse_url($url);
            if(count($parsed)==1&&isset($parsed["path"])) {
                $final[] = "http://".$parsed["path"];
                $final[] = "https://".$parsed["path"];
            }
            elseif(count($parsed)>=2&&isset($parsed["host"])) {
                $final[] = "http://".$parsed["host"] . (isset($parsed["port"])?":{$parsed["port"]}":"");
                $final[] = "https://".$parsed["host"] . (isset($parsed["port"])?":{$parsed["port"]}":"");
            }
            else {
                error_log("skipping unknown format: ".$url." - parsed as    : ".print_r($parsed, true));
            }
        }
        return array_unique($final);
    }

    /**
     * Checks if the string is in JSON format
     *
     * @param string $json
     * @return boolean
     */
    public static function isJson(string $json): bool
    {
        \json_decode($json);
        return (\json_last_error()===JSON_ERROR_NONE);
    }


    /**
     * Fetches both POST and GET params
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    public static function getRequestParams(ServerRequestInterface $request): array
    {
        $data = $request->getQueryParams();
        $post_data = $request->getParsedBody();
        if(static::isJson($post_data)) {
            $post_data = json_decode($post_data, true);
        }
        return array_merge($data, $post_data);
    }

}