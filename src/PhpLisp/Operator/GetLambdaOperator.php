<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class GetLambdaOperator extends AbstractOperator {
    
    public function evaluate ($tree, $scope) {
        $tree = Evaluator::tryEvalExpression($tree, $scope);
        if(Type::isLambda($tree)) {
            return Evaluator::asString($tree);
        } else if(Type::isSymbol($tree)) {
            $treeString = Evaluator::asString($tree);
            if($lambda = Environment::getLambda($scope, $tree->nodeValue)) {
                if(Type::isLambda($lambda)) {
                    return $lambda;
                } else if(Type::isFunc($lambda)) {
                    return "#<compiled-function {$treeString}>";
                }
            } else {
                throw new Exception("Error: {$treeString} is invalid as a function.");
            }
        } else {
            $treeString = Evaluator::asString($tree);
            throw new Exception("Error: {$treeString} is invalid as a function.");
        }
    }
}