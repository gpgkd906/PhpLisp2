<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;

class SymbolEvaluator extends AbstractEvaluator {

    public static function evaluate (Expression $node, $scope) {
        if(($result = Environment::getSymbol($scope, $node->nodeValue)) !== null) {
            return $result;
        } else {
            $nodeString = self::asString($node);
            throw new Exception("Error: The variable {$nodeString} is unbound.");
        }
    }
    
}