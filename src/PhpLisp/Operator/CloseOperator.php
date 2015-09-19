<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class CloseOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        $tree = Evaluator::tryEvalExpression($tree, $scope);
        $resource = Evaluator::asRaw(Evaluator::evaluate($tree, $scope));
        if(!is_resource($resource)) {
            throw new Exception("Error: {$symbol} is invalid resource.");                    
        }
        fclose($resource);
        return Expression::$trueInstance;
    }
}