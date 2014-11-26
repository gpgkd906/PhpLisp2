<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Evaluator\SymbolEvaluator as SymbolEvaluator;

class MacroExpandOperator extends AbstractOperator {

    public $name = "macroexpand-1";

    public function evaluate ($tree, $scope) {
        $tree = Evaluator::evaluate($tree, $scope);
        Environment::write(Evaluator::asString($tree->nodeValue));
        Environment::write(Environment::$eol);
        return Expression::$trueInstance;
    }
}