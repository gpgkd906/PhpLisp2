<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Exception\EvalException as Exception;

class LambdaEvaluator extends AbstractEvaluator {

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
        static::bindParam($lambda->leftLeaf, $param, $lambdaName, $scope);
        $result = static::callBody($lambda->rightLeaf, $lambdaName, $scope);
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
        $poffset = 0;
        $lpoffset = 0;
        $optinal = false;
        while($lpoffset < $lpSize) {
            $lp = $lambdaParam->getAt($lpoffset);
            if(!Type::isSymbol($lp)) {
                $nodeString = Evaluator::asString($lp);
                throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
            }
            switch($lp->nodeValue) {
            case "&optional":
                $lpoffset = $lpoffset + 1;
                $lp = $lambdaParam->getAt($lpoffset);
                $default =  Expression::$nilInstance;
                if(Type::isExpression($lp) || Type::isCons($lp)) {
                    $default = $lp->rightLeaf;
                    $lp = $lp->leftLeaf;
                }
                $p = $param->shift($default);
                $optinal = true;
                break;
            case "&rest":
                $lpoffset = $lpoffset + 1;
                $lp = $lambdaParam->getAt($lpoffset);
                $default = Expression::$nilInstance;
                $p = new Expression(null, Type::Expression, Expression::$quoteInstance, $param->toExpression());
                $optinal = true;
                break;
            case "&key":

                $optinal = true;
                break;
            default:
                $p = $param->shift(null);
                break;
            }
            if($p === null) {
                throw new Exception("Error: {$lambdaName} [or a callee] requires more than {$poffset} arguments.");
            }
            if(Type::isExpression($p)) {
                //パラメタがS式である場合，lambda実行場所のスコープではなく
                //パラメタが定義した場所のスコープで評価しないといけません
                $p = ExpressionEvaluator::evaluate($p, $scope);
            }
            //パラメタ評価後の結果をlambda実行場所のスコープに約束する
            Environment::setSymbol($scope, Evaluator::asString($lp), $p);
            $lpoffset = $lpoffset + 1;
            $poffset = $poffset + 1;
        }
        if($optinal === false) {
            $testp = $param->shift(null);
            if(Type::isLispExpression($testp)) {
                throw new Exception("Error: {$lambdaName} [or a callee] requires less than {$pSize} arguments.");
            }
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