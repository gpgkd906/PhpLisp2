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

class TransformExpandListOperator extends AbstractOperator {

    public $name = "transform.expandlist";

    public function evaluate ($tree, $scope) {
        if($scope !== "transform.backquote") {
            throw new Exception("Error: A comma has appeared out of a backquote.");
        }
        if(Type::isSymbol($tree)) {
            $node = Stack::fromExpression(SymbolEvaluator::evaluate($tree, $scope));
        } else {
            $node = Stack::fromExpression($tree);
        }
        return $node;
    }
}