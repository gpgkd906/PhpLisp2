<?php
namespace PhpLisp\Expression;

use PhpLisp\Environment\Debug as Debug;

class Stack {
    
    private $stack = array();
    
    public function push ($unit) {
        $this->stack[] = $unit;
    }

    public function pushWithoutNull($unit) {
        if( !Type::isNull($unit) ) {
            $this->stack[] = $unit;
        }
    }

    public function pop () {
        return array_pop($this->stack);
    }

    public function unshift ($unit) {
        return array_unshift($this->stack, $unit);
    }

    public function shift () {
        return array_shift($this->stack);
    }

    public function size () {
        return count($this->stack);
    }
    
    public function rest () {
        if (empty($this->stack) ) {
            return Expression::$nilInstance;
        } else {
            return $this->stack;
        }
    }

    public function hasInstance ($object) {
        return is_a($object, __CLASS__);
    }

    public function toExpression () {
        $stack = $this->stack;
        $values = array();
        foreach($stack as $exp) {
            $values[] = $exp->rawValue;
        }
        $values = "(" . join(" ", $values) . ")";
        $left = $this->shift();
        if($this->size() > 1) {
            $node = new Expression($values, Type::Expression, $left, $this);
        } else {
            $right = $this->pop();
            $node = new Expression($values, Type::Expression, $left, $right);
        }
        return $node;
    }

    public static function fromExpression($node) {
        $stack = new self;
        if(Type::isExpression($node)) {
            $stack->push( $node->leftLeaf );
            $right = $node->rightLeaf;
            if(Type::isStack($right)) {
                $rest = $right->rest();
                foreach($rest as $unit) {
                    $stack->pushWithoutNull($unit);
                }
            } else {
                $stack->pushWithoutNull( $right );
            }
        } else {
            $stack->pushWithoutNull( $node );
        }
        return $stack;
    }
    
}