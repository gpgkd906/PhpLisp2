<?php
namespace PhpLisp\Type;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Environment\ScopaChain as ScopaChain;

interface TypeInterface {
    
    public function toString();

    public function getCar();

    public function getCdr();

    public function getValue();
    
    public function setCar();

    public function setCdr();

    public function setValue();

    public function execute(ScopaChain $scope);
}
