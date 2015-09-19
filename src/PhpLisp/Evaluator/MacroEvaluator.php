<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Exception\EvalException as Exception;

class MacroEvaluator extends LambdaEvaluator {

    //マクロ処理は基本的にラムダ処理と同じだが、パラメタの処理は少々違います
    //ラムダ処理の場合、パラメタをラムダスコープに約束する前に評価する必要があります。
    //マクロ処理の場合、パラメタは置換するだけなので、評価する必要がありません
    public static function bindParam($scope, $lp, $p) { 
        Environment::setSymbol($scope, Evaluator::asString($lp), $p);
    }
    
    public static function restParam($lpoffset, $lambdaParam, $param) {
        $lpoffset = $lpoffset + 1;
        $lp = $lambdaParam->getAt($lpoffset);
        $default = Expression::$nilInstance;
        $p = $param->toExpression();
        return array($lpoffset, $lp, $p);
    }

    
}