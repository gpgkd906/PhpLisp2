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
        static::bindParams($lambda->leftLeaf, $param, $lambdaName, $scope);
        $result = static::callBody($lambda->rightLeaf, $lambdaName, $scope);
        return $result;
    }

    public static function bindParams($lambdaParam, $param, $lambdaName, $scope) {
        if(Type::isExpression($param)) {
            $tmp = new Stack;
            $tmp->push($param);
            $param = $tmp;
        }
        list($lambdaParam, $param) = static::preParam($lambdaParam, $param);
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
                list($lpoffset, $lp, $p) = static::optionalParam($lpoffset, $lambdaParam, $param);
                $optinal = true;
                break;
            case "&rest":
                list($lpoffset, $lp, $p) = static::restParam($lpoffset, $lambdaParam, $param);
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
            static::bindParam($scope, $lp, $p);
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

    public static function preParam($lambdaParam, $param) {
        if(Type::isLispExpression($lambdaParam)) {
            $lambdaParam = Stack::fromExpression($lambdaParam);
        }
        if(Type::isLispExpression($param)) {
            $param = Stack::fromExpression($param);
        }
        return array($lambdaParam, $param);
    }

    public static function optionalParam($lpoffset, $lambdaParam, $param) {
        $lpoffset = $lpoffset + 1;
        $lp = $lambdaParam->getAt($lpoffset);
        $default =  Expression::$nilInstance;
        if(Type::isExpression($lp) || Type::isCons($lp)) {
            $default = $lp->rightLeaf;
            $lp = $lp->leftLeaf;
        }
        $p = $param->shift($default);
        return array($lpoffset, $lp, $p);
    }

    public static function restParam($lpoffset, $lambdaParam, $param) {
        $lpoffset = $lpoffset + 1;
        $lp = $lambdaParam->getAt($lpoffset);
        $default = Expression::$nilInstance;
        $p = new Expression(null, Type::Expression, Expression::$quoteInstance, $param->toExpression());
        return array($lpoffset, $lp, $p);
    }

    public static function keyParam() {
        
    }

    public static function bindParam($scope, $lp, $p) {
        if(Type::isExpression($p)) {
            //パラメタが約束する前に評価が必要です
            $p = ExpressionEvaluator::evaluate($p, $scope);
        }
        //パラメタ評価後の結果をlambda実行場所のスコープに約束する
        Environment::setSymbol($scope, Evaluator::asString($lp), $p);        
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