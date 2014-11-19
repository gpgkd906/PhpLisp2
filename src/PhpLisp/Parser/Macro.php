<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evalutor\Evaluator as Evaluator;
use PhpLisp\Environment\SymbolTable as SymbolTable;
use PhpLisp\Expression as Expression;
use PhpLisp\Exception\ParseException as Exception;

class Macro {
    public static $macroTable;
    
    public static function initialization() {
        self::$macroTable = new SymbolTable;
    }

    public static function isMacroRepl ($repl) {
        
    }

    public static function def ($repl) {
        
    }

    public static function isDefined ($symbol) {
        
    }

    public static function deform ($repl) {
        
    }
}
