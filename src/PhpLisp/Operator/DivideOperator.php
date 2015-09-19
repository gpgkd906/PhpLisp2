<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class DivideOperator extends AbstractOperator {

    public $name = "/";

    public function evaluate ($tree, $scope) {
        return Evaluator::reduce($tree, function($res, $node, $scope) {
            if(Type::isNull($res)) {
                return $node;
            }
            return new Expression(Evaluator::asNumber($res, $scope) / Evaluator::asNumber($node, $scope), Type::Scalar);
        }, null, $scope);
    }
}