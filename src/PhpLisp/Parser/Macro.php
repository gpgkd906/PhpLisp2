<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Evaluator\LambdaEvaluator as LambdaEvaluator;
use PhpLisp\Environment\SymbolTable as SymbolTable;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Exception\ParseException as Exception;

/**
 * parse段階でのlisp自分自身による構文木変換
 */
class Macro {
    public static $macroTable;
    public static $scope = "macro";
    
    public static function initialization() {
        self::$macroTable = new SymbolTable;
    }

    public static function getMacro ($symbol) {
        return self::$macroTable->get($symbol);
    }

    public static function define ($stack) {
        if(!Type::isStack($stack)) {
            throw new Exception("[DEFMACRO] Error: Too few arguments.");
        }
        if($stack->size() < 3) {
            throw new Exception("[DEFMACRO] Error: Too few arguments.");
        }
        $symbol = $stack->shift()->nodeValue;
        $param = Stack::fromExpression($stack->shift());
        $macro = new Expression("MACRO", Type::Lambda, $param, $stack);
        self::$macroTable->set($symbol, $macro);
    }

    public static function deform ($node, $sentence, $sentence_left, $sentence_right) {
        $left = $node->leftLeaf;
        $right = $node->rightLeaf;
        if(Type::isSymbol($left)) {
            if(Evaluator::asString($left) === "DEFMACRO") { 
                self::define($right);
                return null;
            }
            if($macro = self::getMacro($left->nodeValue)) {
                $name = $left->nodeValue;
                return $result = LambdaEvaluator::apply($macro, $right, $name, self::$scope);
            }
        }
        return $node;
    }
}
