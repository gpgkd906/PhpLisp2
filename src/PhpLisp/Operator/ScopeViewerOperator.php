<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Evaluator\ExpressionEvaluator as ExpressionEvaluator;
use PhpLisp\Evaluator\SymbolEvaluator as SymbolEvaluator;

class ScopeViewerOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        var_dump($scope);
    }

}