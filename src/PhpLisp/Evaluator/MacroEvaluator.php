<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Exception\EvalException as Exception;

class MacroEvaluator extends AbstractEvaluator {

    public static function evaluate (Expression $node, $scope) {

    }
    
    public static function apply($lambda, $param, $lambdaName, $scope) {
        $lambdascope = Environment::generateUniqueId($lambdaName);
        $scope[] = $lambdascope;
        if(Type::isNull($lambda->leftLeaf)) {
            throw new Exception("Error: No lambda list.");
        }
        if(Type::isNull($lambda->rightLeaf)) {
            return Expression::$nilExpression;
        }
        self::bindParam($lambda->leftLeaf, $param, $lambdaName, $scope);
        $result = self::callBody($lambda->rightLeaf, $lambdaName, $scope);
        return $result;
    }

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
            /* if(Type::isExpression($p)) { */
            /*     //パラメタがS式である場合，lambda実行場所のスコープではなく */
            /*     //パラメタが定義した場所のスコープで評価しないといけません */
            /*     $p = ExpressionEvaluator::evaluate($p, $scope); */
            /* } */
            //パラメタ評価後の結果をlambda実行場所のスコープに約束する
            Environment::setSymbol($scope, Evaluator::asString($lp), $p);
            $offset = $offset + 1;
        }
    }
    
    public static function callBody($lambdaBody, $lambdaName, $scope) {
        $offset = 0;
        do {
            $node = $lambdaBody->getAt($offset);
            if(Type::isNull($node)) {
                break;
            }
            if(Type::isExpression($node)) {
                $node = ExpressionEvaluator::evaluate($node, $scope);
            }
            $result = $node;
            $offset = $offset + 1;
        } while (true);
        return $result;
    }


}