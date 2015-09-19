<?php

namespace PhpLisp\Environment;

use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Exception\EvalException as Exception;

class Debug {
    public static $mode = false;
    public static $limit = null;
    
    //dump expression
    public static function d ($expression) {
        if(self::$mode === false) {
            return false;
        }
        if(isset(self::$limit)){
            if($limit < 0) {
                return false;
            }
            $limit = $limit - 1;
        }
        print_r(array(
            "type" => $expression->nodeTypeLabel,
            "value" => $expression->nodeValue,
        ));
    }

    //print_r and stop
    public static function p () {
        if(self::$mode === false) {
            return false;
        }
        if(isset(self::$limit)){
            if($limit < 0) {
                return false;
            }
            $limit = $limit - 1;
        }
        print_r(func_get_args());
        exit();
    }

    //print_r and through out
    public static function t () {
        if(self::$mode === false) {
            return false;
        }
        if(isset(self::$limit)){
            if($limit < 0) {
                return false;
            }
            $limit = $limit - 1;
        }
        print_r(func_get_args());
    }
}

