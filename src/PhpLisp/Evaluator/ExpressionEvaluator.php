<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Exception\EvalException as Exception;

class ExpressionEvaluator extends AbstractEvaluator {

    public static function evaluate (Expression $tree, $scope) {
        $result = false;
        $left = $tree->leftLeaf;
        $right = $tree->rightLeaf;
        if( Type::isExpression($left) ) {
            $left = self::evaluate($left, $scope);
        }
        if( Type::isSymbol($left) ) {
            $node = Environment::getLambda($scope, $left->nodeValue);
        } else if (Type::isLambda($left)) {
            $node = $left;
        }
        
        if( !isset($node) || false === $node ) {
            $nodeString = self::asString($left);
            throw new Exception("Error: {$nodeString} is invalid as a function.");
        }
        if( Type::isFunc($node) ) {
            $result = call_user_func($node->rightLeaf, $right, $tree, $scope);
        } else if(Type::isLambda($node)) {
            $result = LambdaEvaluator::apply($node, $right, self::asString($left), $scope);
        } else {
            $nodeString = self::asString($left);
            throw new Exception("Error: The function {$nodeString} is undefined.");
        }
        return $result;

    }
    
}