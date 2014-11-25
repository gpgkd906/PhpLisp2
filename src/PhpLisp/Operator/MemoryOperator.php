<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class MemoryOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
		$usage = sprintf('%01.2f Byte', memory_get_usage() );
		$usage_human = sprintf('%01.2f MB', memory_get_usage() / 1048576);
        Environment::write($usage);
        Environment::write(Environment::$eol);
        Environment::write($usage_human);
        Environment::write(Environment::$eol);
    }
}