<?php

namespace PhpLisp\Expression;

class Expression {

    public $nodeValue = null;
    public $rawValue = null;
    public $leftLeaf = null;
    public $rightLeaf = null;
    public $nodeType = null;
    public $nodeTypeLabel = null;
    public $refCount = 0;
    public $isRef = false;

    public static $nilInstance;
    public static $trueInstance;
    
    public function __construct($nodeValue = null, $nodeType = null, $leftLeaf = null, $rightLeaf = null) {
        if(isset($nodeValue)) {
            $this->nodeValue = $nodeValue;
        }
        if(isset($nodeType)) {
            $this->setType($nodeType);
        }
        if(isset($leftLeaf)) {
            $this->leftLeaf = $leftLeaf;
        }
        if(isset($rightLeaf)) {
            $this->rightLeaf = $rightLeaf;
        }
    }

    public function setType($nodeType) {
        $this->nodeType = $nodeType;
        $this->nodeTypeLabel = Type::$typeTable[$nodeType];        
    }
}