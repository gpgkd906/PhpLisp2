<?php

namespace PhpLisp\Expression;

use PhpLisp\Environment\Debug as Debug;

class Type {
    const Quote = 1;
    const Expression = 2;
    const Scalar = 3;
    const Symbol = 4;
    const SymbolSymbol = 5;
    const Cons = 6;
    const True = 7;
    const Nil = 8;
    const Func = 9;
    const Lambda = 10;
    const Stream = 11;

    public static $typeTable = array(
        "undefine", "quote", "expression", "scalar", "symbol", "symbol-symbol", "cons", "true", "nil", "func", "lambda", "stream"
    );

    public static function isScalar ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Scalar;
    }

    public static function isSymbol ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Symbol;
    }

    public static function isLispExpression ($node) {
        return is_a($node, "PhpLisp\Expression\Expression");
    }
    
    public static function isList ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        //T-expression is also not a list
        //but nil-expression is a list
        if($node->nodeType === self::True) {
            return false;
        }
        return true;
    }
    
    public static function isQuote ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Quote;
    }

    public static function isNull ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Nil;
    }

    public static function isTrue ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::True;
    }

    public static function isExpression ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Expression;
    }
    
    public static function isFunc ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Func;
    }

    public static function isLambda ($node) {
        if(!self::isLispExpression($node)) {
            return false;
        }
        return $node->nodeType === self::Lambda;
    }
    
}