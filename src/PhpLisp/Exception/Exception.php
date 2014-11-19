<?php

namespace PhpLisp\Exception;

abstract class Exception extends \Exception {
    
    abstract public function getOriginMessage();
}