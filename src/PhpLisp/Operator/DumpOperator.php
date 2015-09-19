<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class DumpOperator extends AbstractOperator {
    
    public function evaluate ($tree, $scope) {
        if(Type::isSymbol($tree)) {
            if($node = Environment::getSymbol($scope, Evaluator::asString($tree))) {
                return Debug::t($node);
            } else if($node = Environment::getLambda($scope, Evaluator::asString($tree))) {
                return Debug::t($node);
            }
        }
        return Debug::t($tree);        
    }
}