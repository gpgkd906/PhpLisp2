<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;

class AtomOperator extends AbstractOperator {

    public function evaluate ($tree, $scope) {
        $tree = Evaluator::tryEvalExpression($tree, $scope);
        $isAtom = Type::isScalar($tree) || Type::isSymbol($tree) || Type::isNull($tree) || Type::isTrue($tree);
        return $isAtom ? Expression::$trueInstance : Expression::$nilInstance;
    }
}