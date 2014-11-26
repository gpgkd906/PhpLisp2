<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class TransformBackQuoteOperator extends AbstractOperator {

    public $name = "transform.backquote";

    public function evaluate ($tree, $scope) {
        if(Type::isSymbol($tree)) {
            $nodeValue = "(quote " . $tree->nodeValue . ")";
            $node = new Expression($nodeValue, Type::Expression, Expression::$quoteInstance, $tree);
        } else if(Type::isLispExpression($tree)) {
            $stack = Stack::fromExpression($tree);
            $size = $stack->size();
            if($size === 1) {
                $node = new Expression(null, Type::Expression, Expression::$quoteInstance, $tree);
            } else {
                while ($size --> 0) {
                    $unit = $stack->shift();
                    if(Type::isSymbol($unit)) {
                        $unit = new Expression(null, Type::Expression, Expression::$quoteInstance, $unit);
                        $stack->push($unit);
                    } else if(Type::isExpression($unit)) {
                        $scope[] = $this->name;
                        $obj = Evaluator::evaluate($unit, $scope);
                        if(Type::isStack($obj)) {
                            $objSize = $obj->size();
                            while($objSize --> 0) {
                                $unit = $obj->shift();
                                $unit = new Expression(null, Type::Expression, Expression::$quoteInstance, $unit);
                                $stack->push($unit);
                            }
                        } else {
                            $unit = $obj;
                            $unit = new Expression(null, Type::Expression, Expression::$quoteInstance, $unit);
                            $stack->push($unit);
                        }
                    }
                }
            }
            $node = new Expression(null, Type::Expression, Expression::$listInstance, $stack);
        }
        return Evaluator::evaluate($node, $scope);
    }
}