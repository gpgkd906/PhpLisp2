<?php
namespace PhpLisp\Type;

use PhpLisp\Environment\ScopeChain as ScopeChain;

abstract class AbstractType implements TypeInterface {

    public $refCount = 0;
    public $isRef = false;
    protected $nodeValue = null;
    protected $rawValue = null;
    protected $car = null;
    protected $cdr = null;
    
    public function __construct($value)
    {
        $this->setValue($value);
    }
    
    public function setValue($rawValue) {
        $this->nodeValue = $rawValue;
        $this->rawValue = $rawValue;
    }

    public function getValue()
    {
        return $this->rawValue;
    }

    public function getNodeValue()
    {
        return $this->nodeValue;
    }

    public function toString()
    {
        return $this->getNodeValue();
    }

    abstract public function getCar();
    
    abstract public function getCdr();

    abstract public function setCar();
    
    abstract public function setCdr();

    abstract public function execute(ScopeChain $scope);
}
