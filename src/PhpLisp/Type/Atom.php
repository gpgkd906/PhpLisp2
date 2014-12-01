<?php
namespace PhpLisp\Type;

class Atom extends AbstractType {

    public $nodeValue = null;    

    public function __construct($value) {
        $this->nodeValue = $value;
    }
    
}
