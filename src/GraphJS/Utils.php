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

class Utils
{
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

}