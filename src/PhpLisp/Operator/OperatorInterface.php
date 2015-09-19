<?php

namespace PhpLisp\Operator;

interface OperatorInterface {
    
    public function evaluate($tree, $scope);
    
}