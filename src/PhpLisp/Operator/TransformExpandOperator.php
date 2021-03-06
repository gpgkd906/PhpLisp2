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

class TransformExpandOperator extends AbstractOperator {

    public $name = "transform.expand";

    public function evaluate ($tree, $scope) {
        if(!in_array("transform.backquote", $scope)) {
            throw new Exception("Error: A comma has appeared out of a backquote.");
        } 
       if(Type::isSymbol($tree)) {
           $node = SymbolEvaluator::evaluate($tree, $scope);
        } else {
            $node = Stack::fromExpression($tree);
        }
        return $node;
    }
}