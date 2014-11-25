<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class GenSymOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        $uniqid = Environment::generateUniqueId("#:G");
        $symbol = new Expression($uniqid, Type::Symbol);
        return $symbol;
    }
}