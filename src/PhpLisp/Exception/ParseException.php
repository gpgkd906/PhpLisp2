<?php

namespace PhpLisp\Exception;

use PhpLisp\Parser\Reader as Reader;

class ParseException extends Exception {

    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
        Reader::clearSentence();
    }
    
    public function getOriginMessage() {
        $message = $this->getMessage();
        return $message . PHP_EOL . "Broken at Parse.";
    }
}
