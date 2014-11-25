<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class EqlOperator extends AbstractOperator {

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
        $left = Evaluator::tryEvalSymbol($left, $scope);
        $left = Evaluator::tryEvalExpression($left, $scope);
        $right = Evaluator::tryEvalExpression($right, $scope);
        //型チェック
        if($left->nodeType !== $right->nodeType) {
            return Expression::$nilInstance;
        }
        //値チェック
        $left = (string) $left->nodeValue;
        $right = (string) $right->nodeValue;
        if($left === $right) {
            return Expression::$trueInstance;
        } else {
            return Expression::$nilInstance;
        }
        
    }
}