<?php
namespace PhpLisp\Environment;

use PhpLisp\Environment\Debug as Debug;

class Stack {
    
    private $stack = array();
    
    public function push ($unit) {
        $this->stack[] = $unit;
    }

    public function pop () {
        return array_pop($this->stack);
    }

    public function unshift ($unit) {
        return array_unshift($this->stack, $unit);
    }

    public function shift ($unit) {
        return array_shift($this->stack, $unit);
    }

    public function size () {
        return count($this->stack);
    }

    public function rest () {
        return $this->stack;
    }

}