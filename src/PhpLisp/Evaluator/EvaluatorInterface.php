<?php
namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Expression as Expression;

interface EvaluatorInterface {
    
    public static function evaluate (Expression $node, $scope);

}