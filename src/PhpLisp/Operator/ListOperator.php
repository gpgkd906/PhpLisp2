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

class ListOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        
        if(Type::isStack($tree)) {
            $size = $tree->size();
            while($size --> 0) {
                $unit = $tree->shift();
                if(Type::isExpression($unit)) {
                    $unit = ExpressionEvaluator::evaluate($unit, $scope);
                } else if(Type::isSymbol($unit)) {
                    $unit = SymbolEvaluator::evaluate($unit, $scope);                            
                }
                $tree->push($unit);
            }
            return $tree->toExpression();
        } else if(Type::isExpression($tree) ) {
            $tree = ExpressionEvaluator::evaluate($tree, $scope);
        } else if(Type::isSymbol($tree) ) {
            $tree = SymbolEvaluator::evaluate($tree, $scope);
        }
        $node = new Expression($nodeValue, Type::Cons, $tree, Expression::$nilInstance);
        return $node;
    }
}