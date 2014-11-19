<?php

namespace PhpLisp\Exception;

class ParseException extends Exception {
    
    public function getOriginMessage() {
        $message = $this->getMessage();
        return $message . PHP_EOL . "Broken at Parse.";
    }
}
