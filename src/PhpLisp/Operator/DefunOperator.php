<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class DefunOperator extends AbstractOperator {

    public $name = "defun";

    public function evaluate ($tree, $scope) {
        if( !Type::isStack($tree) ) {
            throw new Exception("Error: Too few arguments.");
        }
        if( $tree->size() < 2 ) {
            throw new Exception("Error: Too few arguments.");
        }
        $symbol = $tree->shift();
        if( !Type::isSymbol($symbol) ) {
            throw new Exception("Error: {Evaluator::asString($symbol)} is not of type SYMBOL.");
        }
        $lambdaParam = Stack::fromExpression($tree->shift());
        $lambdaBody = $tree;
        $nodeValue = join("", array(
            "(LAMBDA-BLOCK ",
            $symbol->nodeValue,
            " (",
            $lambdaParam->toString(),
            ") ",
            $tree->toString(),
            ")"));
        $lambda = new Expression($nodeValue, Type::Lambda, $lambdaParam, $lambdaBody);
        return Environment::setLambda($scope, Evaluator::asString($symbol), $lambda);        
    }
}