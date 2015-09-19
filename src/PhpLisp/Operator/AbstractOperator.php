<?php

namespace PhpLisp\Operator;

use PhpLisp\Expression\Type as Type;

abstract class AbstractOperator implements OperatorInterface {
    
    public $name = null;

    public $type;

    public function __construct() {
        if($this->name === null) {
            $name = get_class($this);
            // PhpLisp\Operator\CarOperator => CarOperator
            $name = str_replace('PhpLisp\\Operator\\', '', $name);
            // CarOperator => Car
            $name = substr_replace($name, "", -8);
            $this->name = $name;
        }
        $this->type = Type::Func;
    }
    
}
