<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 24.01.24
 * Time: 09:10
 */

namespace Science;


class Tools {

    public static function isJson($value) {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static  function isInt($value) {
        if (preg_match('/^\d+$/',$value)) {
            return (int) $value;
        }

    }
}