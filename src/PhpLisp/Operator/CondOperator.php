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

class CondOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        $res = Expression::$nilInstance;
        if( !Type::isStack($tree) ) {
            if(Type::isNull($tree)) {
                return $res;
            }
            $tmp = new Stack;
            $tmp->push($tree);
            $tree = $tmp;
        }
        $treeSize = $tree->size();
        $offset = 0;
        while($offset < $treeSize) {
            $unit = $tree->getAt($offset);
            $offset = $offset + 1;
            $left = $unit->leftLeaf;
            $right = $unit->rightLeaf;
                    
            if(Type::isExpression($left)) {
                $left = ExpressionEvaluator::evaluate($left, $scope);
            }
            if(Type::isNull($left)) {
                continue;
            }
            if(Type::isNull($right)) {
                if( Type::isCons($unit) ) {
                    $res = $left;
                } else {
                    $res = $right;
                }
            } else if(Type::isExpression($right)) {
                $res = ExpressionEvaluator::evaluate($right, $scope);
            } else {
                $res = $right;
            }
            break;
        }
        return $res;
        
    }
}