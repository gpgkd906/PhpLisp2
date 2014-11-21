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

    public static function translate ($node, $sentence, $sentence_left, $sentence_right) {
        $node = self::cons($node, $sentence_right);

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
