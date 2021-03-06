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

class ConsOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        if(!Type::isStack($tree)) {
            if(Type::isNull($tree)) {
                throw new Exception("Error: CONS [or a callee] requires more than 0 arguments.");
            } else {
                throw new Exception("Error: CONS [or a callee] requires more than 1 arguments.");
            }
        }
        $treeSize = $tree->size();
        if($treeSize > 2) {
            throw new Exception("Error: CONS [or a callee] requires less than {$treeSize} arguments.");
        }
        $left = $tree->getAt(0);
        $right = $tree->getAt(1);
        $left = Evaluator::tryEvalExpression($left, $scope);
        $right = Evaluator::tryEvalExpression($right, $scope);
        $cons = new Expression(null, Type::Cons, $left, $right);
        return $cons;
    }
}