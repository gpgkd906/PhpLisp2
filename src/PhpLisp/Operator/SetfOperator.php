<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

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
                    $nodeString = Evaluator::asString($symbol);
                    throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
                }
                $symbolKey = Evaluator::asString($symbol);
                $value = Evaluator::tryEvalExpression($value, $scope);
                Environment::setSymbol($scope, $symbolKey, $value);
                $offset = $offset + 2;
            }
            return $value;
        }
        $nodeString = Evaluator::asString($tree);
        throw new Exception("Error: No value for {$nodeString}.");
    }
}