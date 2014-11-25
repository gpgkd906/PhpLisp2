<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class DebugOperator extends AbstractOperator {

    public $name = "debug";

    public function evaluate ($tree, $scope) {
        Debug::$mode = !Type::isNull($tree);
    }
}