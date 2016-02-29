<?php

namespace PhpLisp\Operator;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Expression\Expression as Expression;

class Operator {

    public static $except = array("AbstractOperator.php", "Operator.php", "OperatorInterface.php", ".", "..");
    
    public static function getAll() {
        $current = dirname(__FILE__);
        $res = array();
        $dirHandler = opendir($current);
        while(($file = readdir($dirHandler)) !== false) {
            if(in_array($file, self::$except)) {
                continue;
            }
            if(substr($file, -1) === "~") {
                continue;
            }
            if(substr($file, -1) === "#") {
                continue;
            }
            //fix for windows: Desktop.ini
            if(substr($file, -3) !== "php") {
                continue;
            }
            $name = explode(".", $file)[0];
            $class = 'PhpLisp\\Operator\\' . $name;
            $obj = new $class;
            $res[$obj->name] = new Expression($obj->name, $obj->type, null, array($obj, "evaluate"));
        }
        return $res;
    }

}
