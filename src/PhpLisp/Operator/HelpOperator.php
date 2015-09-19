<?php
namespace PhpLisp\Operator;

use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Exception\EvalException as Exception;

class HelpOperator extends AbstractOperator {
    
    public function evaluate ($tree, $scope) {
        if(!$file = stream_resolve_include_path("../src/PhpLisp/doc/HELP")) {
            if(!$file = stream_resolve_include_path("src/PhpLisp/doc/HELP")) {
                if(!$file = stream_resolve_include_path("PhpLisp/doc/HELP")) {
                    if(!$file = stream_resolve_include_path("doc/HELP")) {
                        throw new Exception("document file missed!");
                    }
                }
            }
        }
        Environment::write(Environment::$eol);
        Environment::write(file_get_contents($file));
        Environment::write(Environment::$eol);
    }


}