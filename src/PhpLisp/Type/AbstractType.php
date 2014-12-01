<?php
namespace PhpLisp\Type;


class AbstractType implements TypeInterface {

    public $refCount = 0;
    public $isRef = false;
    public $typeLabel = null;

    public function setType($nodeType) {
        $this->typeLabel = Type::$typeTable[$nodeType];        
    }
}
