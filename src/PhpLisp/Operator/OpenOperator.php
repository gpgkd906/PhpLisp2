<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class OpenOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        $tree = Evaluator::tryEvalExpression($tree, $scope);
        if(Type::isScalar($tree)) {
            $filename = $tree->nodeValue;
            $mode = "rw";
        } else {
            $filename = Evaluator::asRaw(Evaluator::car($tree, $scope));
            $mode = Evaluator::asRaw(Evaluator::car(Evaluator::cdr($tree, $scope), $scope));
        }
        if(!is_file($filename)) {
            throw new Exception("Error: Cannot open the file {$filename}.");
        }
        $handler = fopen($filename, $mode);
        return new Expression($handler, Type::Stream, null, null);

    }
}