<?php

namespace PhpLisp\Type;

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
    const Macro = 9;    
    const Stream = 10;
    
    const Stack = 21;
    
    public static $typeTable = array(
        "undefine", "expression", "scalar", "symbol", "cons", "true", "nil", "func", "lambda", "macro", "stream");

    public static function isLispExpression ($node) {
        return $node instanceOf Expression;
    }
    
    public static function isAtom ($node) {
        return $node instanceOf Atom;
    }

    public static function isScalar ($node) {
        return $node instanceOf Scalar;
    }

    public static function isSymbol ($node) {
        return $node instanceOf Symbol;
    }

    public static function isCons ($node) {
        return $node instanceOf Cons;
    }

    public static function isList ($node) {
        if(self::isLispExpression($node) || self::isNull($node)) {
            return true;
        }
        return false;
    }
    
    public static function isNull ($node) {
        return $node instanceOf Nil;
    }

    public static function isTrue ($node) {
        return $node instanceOf T;
    }

    public static function isExpression ($node) {
        return $node instanceOf Expression;
    }
    
    public static function isFunc ($node) {
        return $node instanceOf Func;
    }

    public static function isLambda ($node) {
        return $node instanceOf Lambda;
    }

    public static function isStack ($stack) {
        return $stack instanceOf Stack;
    }
    
}