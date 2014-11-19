<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Type as Type;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Exception\EvalException as Exception;

class Lambda {
    
    public static function apply($lambda, $param, $lambdaName) {
        $scope = Environment::generateUniqueId($lambdaName);
        self::bindParamToScope(Evaluator::car($lambda, $scope), $param, $lambdaName, $scope);
        return self::callOnScope(Evaluator::cdr($lambda, $scope), $lambdaName, $scope);
    }

    public static function bindParamToScope($lambdaParam, $param, $lambdaName, $scope) {
        $count = 0;
        $oriLp = $lambdaParam;
        $orip = $param;
        do {
            $count = $count + 1;
            if(Type::isNull($param)) {
                if(!Type::isNull($lambdaParam)) {
                    throw new Exception("Error: {$lambdaName} [or a callee] requires more than {$count} arguments.");
                }
                //thought out
            }
            if(Type::isNull($lambdaParam)) {
                if(!Type::isNull($param)) {
                    throw new Exception("Error: {$lambdaName} [or a callee] requires less than {$count} arguments.");
                }
                //we bind all parameter, now break;
                break;
            }
            $lp = Evaluator::car($lambdaParam, $scope);
            $p = Evaluator::car($param, $scope);
            if(!Type::isSymbol($lp)) {
                $nodeString = Evaluator::asString($node);
                throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
            }
            Environment::setSymbol($scope, Evaluator::asString($lp), $p);
            $lambdaParam = Evaluator::cdr($lambdaParam, $scope);
            $param = Evaluator::cdr($param, $scope);
        } while (true);
    }
    
    public static function callOnScope($lambdaBody, $lambdaName, $scope) {
        $body = Evaluator::unpackQuote($lambdaBody);
        while(!Type::isNull($body)) {
            $node = Evaluator::car($body, $scope);
            $result = Evaluator::evaluate($node, $scope);
            $body = Evaluator::quote(Evaluator::cdr($body, $scope));
        }
        return $result;
    }


}