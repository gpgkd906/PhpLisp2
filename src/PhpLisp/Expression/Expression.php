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
    public static $quoteInstance;
    public static $listInstance;
    
    public function __construct($rawValue = null, $nodeType = null, $leftLeaf = null, $rightLeaf = null) {
        if(isset($rawValue)) {
            $this->setValue($rawValue);
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

    public function setValue($rawValue) {
        $this->nodeValue = $rawValue;
        $this->rawValue = $rawValue;
    }

    public function setLeftLeaf($left) {
        $this->leftLeaf = $left;
        $right = $this->rightLeaf;
        if(Type::isStack($right)) {
            $thisValue = "(" . $left->nodeValue . " " . $right->toString() .")";
        } else {
            if(Type::isScalar($right) || Type::isSymbol($right) || Type::isTrue($right)) {
                $thisValue = "(" . $left->nodeValue . " . " . $right->nodeValue .")";
                $this->setType(Type::Cons);
            } else if(Type::isNull($right)){
                $thisValue = "(" . $left->nodeValue .")";
            } else {
                $thisValue = "(" . $left->nodeValue . " " . $right->nodeValue .")";
            }
        }
        $this->setValue($thisValue);
    }

    public function setRightLeaf($right) {
        $this->rightLeaf = $right;
        if(Type::isStack($right)) {
            $thisValue = "(" . $this->leftLeaf->nodeValue . " " . $right->toString() .")";
        } else {
            if(Type::isScalar($right) || Type::isSymbol($right) || Type::isTrue($right)) {
                $thisValue = "(" . $this->leftLeaf->nodeValue . " . " . $right->nodeValue .")";
                $this->setType(Type::Cons);
            } else if(Type::isNull($right)){
                $thisValue = "(" . $this->leftLeaf->nodeValue .")";
            } else {
                $thisValue = "(" . $this->leftLeaf->nodeValue . " " . $right->nodeValue .")";
            }
        }
        $this->setValue($thisValue);
    }
}