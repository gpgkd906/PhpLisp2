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
            $this->setLeftLeaf( $leftLeaf );
        }
        if(isset($rightLeaf)) {
            $this->setRightLeaf( $rightLeaf );
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
        $this->onSetLeaf();
    }

    public function setRightLeaf($right) {
        $this->rightLeaf = $right;
        $this->onSetLeaf();
    }

    public function onSetLeaf() {
        $left = $this->leftLeaf;
        if(empty($left)) {
            return;
        }
        $right = $this->rightLeaf;
        if(empty($right)) {
            return;
        }
        $leftValue = Type::isStack($left) ? $left->toString() : $left->nodeValue;
        $rightValue = Type::isStack($right) ? $right->toString() : $right->nodeValue;
        if(Type::isCons($this)
        && (Type::isScalar($right) || Type::isSymbol($right) || Type::isTrue($right))) {
            $thisValue = "(" . $leftValue . " . " . $rightValue .")";
            $this->setType(Type::Cons);
        } else if(Type::isNull($right)){
            $thisValue = "(" . $leftValue .")";
        } else {
            $thisValue = "(" . $leftValue . " " . $rightValue .")";
        }
        $this->setValue($thisValue);
    }
}