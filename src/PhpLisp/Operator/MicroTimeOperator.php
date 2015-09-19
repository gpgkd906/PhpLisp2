<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class MicroTimeOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        Environment::write( microtime() );
        Environment::write( Environment::$eol );
    }
}