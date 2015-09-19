<?php
namespace PhpLisp\Type;

use PhpLisp\Exception\EvalException as Exception;

class Atom extends AbstractType {

    public $nodeValue = null;

    public function getCar()
    {
        throw new Exception('Wrong type argument: listp, ' . $this->getValue());
    }
    
    public function getCdr()
    {
        throw new Exception('Wrong type argument: listp, ' . $this->getValue());
    }

    public function setCar()
    {
        throw new Exception('Wrong type argument: listp, ' . $this->getValue());
    }

    public function setCdr()
    {
        throw new Exception('Wrong type argument: listp, ' . $this->getValue());
    }
}
