<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class LambdaOperator extends AbstractOperator {

    public $name = "lambda";

    public function evaluate ($tree, $scope) {
        $lambdaHeader = "LAMBDA-CLOSURE () () ()";
        $lambdaParam = Expression::$nilInstance;
        $lambdaBody = Expression::$nilInstance;
        if( !Type::isStack($tree) ) {
            if( Type::isNull($tree) ) {
                $nodeValue = "(" . $lambdaHeader . ")";
            } else {
                $nodeValue = "(" . $lambdaHeader . " " . Evaluator::asString($tree) . ")";
                $lambdaParam = Stack::fromExpression($tree);
            }
        } else {
            $nodeValue = "(LAMBDA-CLOSURE () () () " . $tree->toString() . ")";
            $lambdaParam = Stack::fromExpression($tree->shift());
            $lambdaBody = $tree;
        }
        return new Expression($nodeValue, Type::Lambda, $lambdaParam, $lambdaBody);
    }
}