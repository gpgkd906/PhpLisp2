<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Environment\SymbolTable as SymbolTable;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Exception\ParseException as Exception;

/**
 * parse段階でのPHPによる構文木変換
 */
class Transform {
    
    public static $scope = "transform";
    
    public static $special = array("'" => "quote", "`" => "transformBackQuote", "#'" => "getLambda", "," => "transformExpand", "@" => "transformExpandList");
    
    public static function translate ($node, $sentence, $sentence_left, $sentence_right) {
        $node = self::cons($node, $sentence_right);
        $nodeValue = $node->leftLeaf->nodeValue;
        if(method_exists(__CLASS__, $nodeValue)) {
            $node = call_user_func("self::" . $nodeValue, $node, $sentence);
        }
        return $node;
    }

    public static function transformBackQuote ($node) {
        $right = $node->rightLeaf;
        if(Type::isSymbol($right)) {
            $node->setValue("(quote " . $right->nodeValue . ")");
            $node->leftLeaf = Parser::read("quote");
        } else if(Type::isLispExpression($right)) {
            $stack = Stack::fromExpression($right);
            $size = $stack->size();
            if($size === 1) {
                $node = new Expression("(quote " . $right->nodeValue . ")", Type::Expression, Parser::read("quote"), $right);
            } else {
                $stack = Evaluator::map($stack, function($node, $scope) {
                    if(Type::isSymbol($node)) {
                        $node = new Expression("(quote " . $node->nodeValue . ")", Type::Expression, Parser::read("quote"), $node);
                    }
                    return $node;
                }, self::$scope);
                $values = array("(list");
                for($offset = 0; $offset < $size; $offset++) {
                    $values[] = $stack->getAt($offset)->nodeValue;
                }
                $nodeValue = join(" ", $values) . ")";
                $node = new Expression($nodeValue, Type::Expression, Parser::read("list"), $stack);
            }
        }
        return $node;
    }

    //cons変換
    public static function cons ($node, $sentence_right) {
        if(!isset($sentence_right[0])) {
            $node->setType(Type::Cons);
        }
        $left = $node->leftLeaf;
        $right = $node->rightLeaf;
        if(Type::isSymbol($right) && $right->nodeValue === ".") {
            throw new Exception("Error: Object missing after dot.");
        }
        if(Type::isStack($right)) {
            $first = $right->getAt(0);
            if(Type::isSymbol($first) && $first->nodeValue === ".") {
                if($right->size() > 2) {
                    throw new Exception("Error: Two objects after dot.");                    
                }
                $right = $right->getAt(1);
                if(Type::isExpression($right) || Type::isCons($right)) {
                    $nodeValue = join(" ", array(
                        "(", 
                        $left->nodeValue, 
                        substr_replace(
                            substr_replace($right->nodeValue, "", -1, 1),
                            "", 0, 1),
                        ")"
                    ));
                    $node->nodeValue = Parser::removeDummySpace($nodeValue);
                } else if(Type::isNull($right)) {
                    $node->nodeValue = "(" . $left->nodeValue . ")";
                }
                $node->rightLeaf = $right;
                $node->setType(Type::Cons);
            }
        }
        return $node;
    }
}
