<?php
namespace PhpLisp\Type;

class Expression extends AbstractType {

    public $nodeValue = null;
    public $rawValue = null;
    public $leftLeaf = null;
    public $rightLeaf = null;
    
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
        if(Type::isCons($this)){
            if(Type::isScalar($right) || Type::isSymbol($right) || Type::isTrue($right)) {
                $thisValue = "(" . $leftValue . " . " . $rightValue .")";
            } else if(Type::isNull($right)){
                $thisValue = "(" . $leftValue .")";
            } else if(Type::isExpression($right) || Type::isCons($right)) {
                $rightValue = substr_replace(substr_replace($right->nodeValue, "", -1, 1), "", 0, 1);
                $thisValue = "(" . $leftValue . " " . $rightValue .")";
            }
            $this->setType(Type::Cons);
            $this->setValue($thisValue);
        } else if (Type::isLambda($this)){
            //nodeValue was setted be DefunOperator/LambdaOperator 
            //do not rewrite the nodeValue
        } else {
            if(Type::isNull($right)){
                $thisValue = "(" . $leftValue .")";
            } else {
                $thisValue = "(" . $leftValue . " " . $rightValue .")";
            }
            $this->setValue($thisValue);
        }
    }
}
