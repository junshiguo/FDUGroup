<?php
/**
 * Created by PhpStorm.
 * User: songrenchu
 */

class RStringHelper {
    public static function substring($str, $start = 0, $limit = null) {

    }

    public static function utf8_substring($str,$start=0, $len = null){
        $len = $len===null? strlen($str): $len;
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$start.'}'. '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', '$1',$str);
    }
}