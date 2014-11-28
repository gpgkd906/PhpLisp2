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

class SetfOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        if(Type::isNull($tree)) {
            return Expression::$nilInstance;
        } else if (Type::isStack($tree)) {
            $treeSize = $tree->size();
            if($treeSize === 0) {
                return Expression::$nilInstance;
            }
            if(($treeSize % 2) !== 0) {
                $lastUnit = $tree->pop();
                $tree->push($lastUnit);
                $nodeString = Evaluator::asString($lastUnit);
                throw new Exception("Error: No value for {$nodeString}.");
            }
            
            $offset = 0;
            while($offset < $treeSize) {
                $symbol = $tree->getAt($offset);
                $value = $tree->getAt($offset + 1);
                if( !Type::isSymbol($symbol) ) {
                    if(Type::isExpression($symbol)) { 
                        return self::setfLeaf($symbol, $value, $scope);
                    }
                    $nodeString = Evaluator::asString($symbol);
                    throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
                } else {
                    $symbolKey = Evaluator::asString($symbol);
                    $value = Evaluator::tryEvalExpression($value, $scope);
                    Environment::setSymbol($scope, $symbolKey, $value);
                    $offset = $offset + 2;
                }
            }
            return $value;
        }
        $nodeString = Evaluator::asString($tree);
        throw new Exception("Error: No value for {$nodeString}.");
    }

    public function setfLeaf($tree, $value, $scope) {
        $action = Evaluator::asString($tree->leftLeaf);
        if($action === "CAR" || $action === "CDR") {
            $target = $tree->rightLeaf;
            if(Type::isSymbol($target)) {
                $target = SymbolEvaluator::evaluate($target, $scope);
            }
            if($action === "CAR") {
                $target->setLeftLeaf($value);
            } else {
                $target->setRightLeaf($value);
            }
            return $value;
        }
        $nodeString = Evaluator::asString($tree);
        throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
    }
}