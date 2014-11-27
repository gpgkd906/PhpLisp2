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

class TransformQuoteExpandOperator extends AbstractOperator {

    public $name = "transform.quoteExpand";
    
    public function evaluate ($tree, $scope) {
        if(!in_array("transform.backquote", $scope)) {
            throw new Exception("Error: A comma has appeared out of a backquote.");
        }
        if(Type::isSymbol($tree)) {
            $node = SymbolEvaluator::evaluate($tree, $scope);
            if(Type::isScalar($node) || Type::isSymbol($node) || Type::isTrue($node)) {
                //do nothing
            } else {
                $node = Stack::fromExpression($node);
                $node = new Expression(null, Type::Expression, Expression::$quoteInstance, $node->toExpression());
            }
        } else {
            $node = Stack::fromExpression($tree);
            $node = new Expression(null, Type::Expression, Expression::$quoteInstance, $node->toExpression());
        }
        return $node;
    }
}