<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Evaluator\MacroEvaluator as MacroEvaluator;
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
    public static $scope;
    
    public static function initialization() {
        //まずマクロ実行時の最外側スコープがrootScopeであることを保証する
        self::$scope = Environment::$rootScope;
        //そして、マクロの実行スコープを追加する
        self::$scope[] = "macro";
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
        $nodeValue = $stack->toString();
        $symbol = $stack->shift()->nodeValue;
        $macro = new Expression($nodeValue, Type::Macro);
        self::$macroTable->set($symbol, $macro);
    }

    public static function expand ($node) {
        $left = $node->leftLeaf;
        $right = $node->rightLeaf;
        if(Type::isSymbol($left)) {
            if(Evaluator::asString($left) === "DEFMACRO") { 
                self::define($right);
                //return nullはつまり構文木からこのノードを削除すること
                return null;
            }
            if($macro = self::getMacro($left->nodeValue)) {
                $name = $left->nodeValue;
                //マクロはreParserが必要※クーロンを作るため
                $clone = self::reParse($macro->nodeValue);
                $result = MacroEvaluator::apply($clone, $right, $name, self::$scope);
                return self::expand($result);
            }
        }
        return $node;
    }

    public static function reParse($nodeValue) {
        $stack = Parser::read($nodeValue);
        $symbol = $stack->shift()->nodeValue;
        $param = Stack::fromExpression($stack->shift());
        $macro = new Expression($nodeValue, Type::Lambda, $param, $stack);
        return $macro;
    }
}
