<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Parser\Parser as Parser;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;

class Evaluator {

    public static function evalTree($ast, $scope) {
        Debug::t($ast);
        return false;
        if(Type::isQuote($ast)) {
            return self::asString($ast);
        }
        //pass 1
        $result = self::evaluate($ast, $scope);
        if(Type::isLispExpression($result)) {
            if(Type::isQuote($result)) {
                return self::asString($result);
            }
            //pass 2
            $nestResult = self::evaluate($result, $scope);
            if(Type::isLispExpression($nestResult)) {
                //pass 3
                return self::asString($nestResult);
            }
            return $nestResult;
        }
        return $result;
    }
    
    public static function car ($node, $scope) {
        if(Type::isList($node)) {
            if(Type::isQuote($node)) {
                $node = self::evaluate($node, $scope);
            }
            if(Type::isNull($node)) {
                return Expression::$nilInstance;
            }
            if(isset($node->leftLeaf)) {
                return $node->leftLeaf;
            }
            $nodeString = self::asString($node);            
            throw new Exception("Error: {$nodeString} is not of type List.");
        }
        if(Type::isTrue($node)) {
            $nodeString = self::asString($node);
            throw new Exception("Error: {$nodeString} is not of type List.");
        } else {
            throw new Exception("Error: {$node} is not of type List.");
        }
    }

    public static function cdr ($node, $scope) {
        if(Type::isList($node)) {
            if(Type::isNull($node)) {
                return Expression::$nilInstance;
            }
            if(Type::isQuote($node)) {
                $node = self::evaluate($node, $scope);
                //Debug::t($node);
            }
            if(isset($node->rightLeaf)) {
                //関数でない式(例えばQuoteデータの右ノード)の場合
                //もし、その式の右ノードが既にQuoteデータのであれ
                if(Type::isExpression($node) && Type::isQuote($node->rightLeaf)) {
                    return $node->rightLeaf;
                }
                return self::quote($node->rightLeaf);
            }
            $nodeString = self::asString($node);
            throw new Exception("Error: {$nodeString} is not of type List.");
        }
        if(Type::isTrue($node)) {
            $nodeString = self::asString($node);
            throw new Exception("Error: {$nodeString} is not of type List.");
        } else {
            throw new Exception("Error: {$node} is not of type List.");
        }
    }
    
    public static function quote($node) {
        if(Type::isNull($node)) {
            return Expression::$nilInstance;
        }
        $quoteValue = "(" . $node->nodeValue . ")";
        return new Expression($quoteValue, Type::Quote);
    }

    public function asString($node) {
        $raw = self::asRaw($node);
        if(is_string($raw)) {
            return strtoupper($raw);
        }
    }

    public function asRaw($node) {
        if(Type::isLispExpression($node)) {
            $raw = $node->nodeValue;
        } else {
            $raw = $node;
        }
        //　"/'で囲まれる場合は削除
        if(is_string($raw)) {
            if(strpos($raw, "'") !== false) {
                $raw = preg_replace("/^'|'$/", "", $raw);
            }
            if(strpos($raw, '"') !== false) {
                $raw = preg_replace('/^"|"$/', "", $raw);
            }
        }
        return $raw;
    }

    public function asNumber($node, $scope) {
        if(is_numeric($node)) {
            return floatval($node);
        }
        if(Type::isScalar($node)) {
            if(is_numeric($node->nodeValue)) {
                return floatval($node->nodeValue);
            }
            $nodeString = self::asString($node);
            throw new Exception("Error: {$nodeString} is not of type NUMBER.");
        } else {
            $result = self::evaluate($node, $scope);
            if(is_numeric($result)) {
                return floatval($result);
            }
            return self::asNumber($result, $scope);
        }
    }

    public static function unpackQuote($node) {
        if(Type::isQuote($node)) {
            return Parser::read($node->nodeValue, false);
        } else {
            return $node;
        }
    }

    public static function tryEvalExpression($tree, $scope) {
        if(!Type::isExpression($tree)) {
            return $tree;
        }
        return self::evalExpression($tree, $scope);
    }

    public static function evalExpression($tree, $scope) {
        $result = false;
        $left = $tree->leftLeaf;
        $right = $tree->rightLeaf;
        if(Type::isSymbol($left)) {
            $node = Environment::getLambda($scope, $left->nodeValue);
        } else {
            if (Type::isExpression($left)) {
                $nodeString = self::asString($left);
                throw new Exception("Error: ({$nodeString}) is invalid as a function.");
            } else if (Type::isQuote($left)) {
                $nodeString = self::asString($left);
                throw new Exception("Error: (QUOTE {$nodeString}) is invalid as a function.");
            } else if (Type::isScalar($left)) {
                return self::quote($tree);
            } else if (Type::isTrue($left)) {
                return $left;
            } else if (Type::isLambda($left)) {
                if(!Type::isQuote($right)) {
                    $right = self::quote($right);
                }
                $node = $left;
            } else {
                return $tree;
            }
        }
        if( Type::isFunc($node) ) {
            $result = call_user_func($node->rightLeaf, $right, $tree, $scope);
        } else if(Type::isLambda($node)) {
            if(!Type::isQuote($right)) {
                $right = self::quote($right);
            }
            $result = Lambda::apply($node, $right, self::asString($left));
        } else {
            $nodeString = self::asString($left);
            throw new Exception("Error: The function {$nodeString} is undefined.");
        }
        return $result;
    }
    
    public static function evaluate($tree, $scope) {
        $result = false;
        $left = $tree->leftLeaf;
        $right = $tree->rightLeaf;
        switch($tree->nodeType) {
        case Type::Symbol:
            if($node = Environment::getSymbol($scope, $tree->nodeValue)) {
                $result = $node;
            } else {
                $treeString = self::asString($tree);
                throw new Exception("Error: The variable {$treeString} is unbound.");
            }
            break;
        case Type::Quote:
            $result = self::unpackQuote($tree);
            break;
        case Type::Scalar:
            return self::asString($tree);
            break;
        case Type::Expression:
            return self::evalExpression($tree, $scope);
            break;
        case Type::Cons:
            Debug::t($tree);
            $result = "not yet implemented: self::evaluate[Cons]";
            break;
        case Type::Nil:
            return Expression::$nilInstance;
            break;
        case Type::True:
            return Expression::$trueInstance;
            break;
        case Type::Stream:
            return $tree;
            break;
        case Type::Lambda:
            //let expression handle it, we thought out here
            continue;
            $result = "not yet implemented: Expression::evaluate[lambda]";
            break;
        default:
            Debug::t($tree);
            $result = "not yet implemented: Expression::evaluate[default]";
            break;
        }
        return $result;
    }

    public static function reduce($tree, $func, $init = null, $scope) {
        if(isset($init)) {
            $res = $init;
        } else {
            $res = Expression::$nilInstance;
        }
        while(!Type::isNull($tree)) {
            //もし既に単一の要素であれば，そのまま返す
            if(Type::isScalar($tree)) {
                return call_user_func($func, $res, $tree, $scope);
            }
            $node = self::car($tree, $scope);
            if(Type::isScalar($node)) {
                $res = call_user_func($func, $res, $node, $scope);
            } else if(Type::isSymbol($node)) {
                //もし$nodeがsymbolであれば，$treeがexpressionになる
                //この場合はまず$nodeを変数と仮定し
                if($testNode = Environment::getSymbol($scope, self::asString($node))) {
                    $node = $testNode;
                    $res = call_user_func($func, $res, $node, $scope);
                } else {
                    //$nodeが変数でない場合は、関数かlambdaとみなして処理する
                    //expressionをまるごと処理したら，$treeをnil-Expressionにして返す
                    $node = self::evaluate($tree, $scope);
                    $res = call_user_func($func, $res, $node, $scope);
                    $tree = Expression::$nilInstance;
                    continue;
                }
            } else {
                $node = self::evaluate($node, $scope);
                $res = call_user_func($func, $res, $node, $scope);
            }
            $tree = self::unpackQuote(self::cdr($tree, $scope));
        }
        return $res;
    }
    

}

