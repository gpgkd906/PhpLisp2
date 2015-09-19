<?php

namespace PhpLisp\Exception;

class EvalException extends Exception {
    
    public function getOriginMessage() {
        $message = $this->getMessage();
        return $message . PHP_EOL . "Broken at Eval.";
    }
}