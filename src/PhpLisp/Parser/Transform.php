<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evalutor\Evaluator as Evaluator;
use PhpLisp\Environment\SymbolTable as SymbolTable;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\ParseException as Exception;

/**
 * parse段階でのPHPによる構文木変換
 */
class Transform {
    
    public static $special = array("'" => "quote", "#'" => "transformfunction", "`" => "transformbackQuote", "," => "transformexpand", "@" => "transformexpandList");
    
    public static function translateExpression ($node, $sentence, $sentence_left, $sentence_right) {
        $node = self::cons($node, $sentence_right);
        return $node;
    }

    public static function translateStack($stack) {
        $stack = self::backQuoteStack($stack);
        return $stack;
    }

    public static function translate($node) {
        $node = self::backQuote($node);
        return $node;
    }


    public static function backQuote ($node) {
        if(Type::isSymbol($node)) {
            Debug::p($node);
            $value = $node->nodeValue;
            if($value[0] === "`") {
                if(!isset($value[1])) {
                    throw new Exception(" End of file during parsing.");
                }
                $value = substr_replace($value, "", 0, 1);
                $node->setValue($value);
                return new Expression("(quote " . $value . ")", Type::Expression, Parser::read("quote"), $node);
            }
        }
        return $node;
    }

    public static function backQuoteStack ($stack) {
        //Debug::p($stack);
        return $stack;
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
