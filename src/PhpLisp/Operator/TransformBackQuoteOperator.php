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
            return $tree;
        } else if (Type::isScalar($tree) || Type::isTrue($tree) || Type::isNull($tree)) {
            $nodeValue = "(quote " . $tree->nodeValue . ")";
            $node = new Expression($nodeValue, Type::Expression, Expression::$quoteInstance, $tree);
            return $node;
        } else if(Type::isLispExpression($tree)){
            if("transform" === substr($tree->leftLeaf->nodeValue, 0, 9) ) {
                $scope[] = $this->name;
                return Evaluator::evaluate($tree, $scope);
            } else {
                $tree->setLeftLeaf( self::evaluate($tree->leftLeaf, $scope) );
                $tree->setRightLeaf( self::evaluate($tree->rightLeaf, $scope) );
                return $tree;
            }
        } else if(Type::isStack($tree)) {
            $treeSize = $tree->size();
            while($treeSize -- > 0) {
                $unit = $tree->shift();
                $unit = self::evaluate( $unit, $scope );
                $tree->push( $unit );
            }
            return $tree;
        }
    }
}