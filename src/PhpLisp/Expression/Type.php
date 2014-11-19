<?php

namespace PhpLisp\Expression;

use PhpLisp\Environment\Debug as Debug;

class Type {
    const Expression = 1;
    const Scalar = 2;
    const Symbol = 3;
    const Cons = 4;
    const True = 5;
    const Nil = 6;
    const Func = 7;
    const Lambda = 8;
    const Stream = 9;
    
    const Stack = 21;
    
    public static $typeTable = array(
        "undefine", "expression", "scalar", "symbol", "cons", "true", "nil", "func", "lambda", "stream", "stack"
    );

    public static function isLispExpression ($node) {
        return $node instanceOf Expression;
    }
    
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

    public static function isStack ($stack) {
        return $stack instanceOf Stack;
    }
    
}