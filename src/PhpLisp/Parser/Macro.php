<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evalutor\Evaluator as Evaluator;
use PhpLisp\Environment\SymbolTable as SymbolTable;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\ParseException as Exception;

/**
 * parse段階でのlisp自分自身による構文木変換
 */
class Macro {
    public static $macroTable;
    
    public static function initialization() {
        self::$macroTable = new SymbolTable;
    }

    public static function isMacro () {
        
    }

    public static function def () {
        
    }

    public static function isDefined ($symbol) {
        
    }

    public static function quote ($node) {
        $nodeValue = "(quote " . $node->nodeValue . ")";
        return new Expression($nodeValue, Type::Expression, Parser::read("quote"), $node);
    }

    public static function deform ($node, $sentence, $sentence_left, $sentence_right) {
        $left = $node->leftLeaf;
        $right = $node->rightLeaf;
        return $node;
        //Debug::p($right->nodeValue === ".", $sentence);
    }
}
