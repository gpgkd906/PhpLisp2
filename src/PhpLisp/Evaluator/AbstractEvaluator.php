<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;

abstract class AbstractEvaluator implements EvaluatorInterface {
    
    abstract public static function evaluate(Expression $node, $scope);
    
    public static function asString ($node) {
        if(Type::isLispExpression($node)) {
            $raw = $node->rawValue;
        } else {
            $raw = $node;
        }
        $raw = (string) $raw;
        return strtoupper($raw);
    }
    
}