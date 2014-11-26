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
    public static function bindParam($lambdaParam, $param, $lambdaName, $scope) {
        if(Type::isExpression($param)) {
            $tmp = new Stack;
            $tmp->push($param);
            $param = $tmp;
        }
        if(Type::isLispExpression($lambdaParam)) {
            $lambdaParam = Stack::fromExpression($lambdaParam);
        }
        if(Type::isLispExpression($param)) {
            $param = Stack::fromExpression($param);
        }
        $lpSize = $lambdaParam->size();
        $pSize = $param->size();
        if($lpSize > $pSize) {
            throw new Exception("Error: {$lambdaName} [or a callee] requires more than {$pSize} arguments.");
        }
        if($lpSize < $pSize) {
            throw new Exception("Error: {$lambdaName} [or a callee] requires less than {$pSize} arguments.");
        }
        $offset = 0;
        while($offset < $lpSize) {
            $lp = $lambdaParam->getAt($offset);
            $p = $param->getAt($offset);
            if(!Type::isSymbol($lp)) {
                $nodeString = Evaluator::asString($lp);
                throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
            }
            Environment::setSymbol($scope, Evaluator::asString($lp), $p);
            $offset = $offset + 1;
        }
    }
}