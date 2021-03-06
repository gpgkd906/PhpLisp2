<?php

namespace PhpLisp\Evaluator;

use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Parser\Parser as Parser;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\Debug as Debug;

class Evaluator extends AbstractEvaluator {

    public static function evalTree($ast, $scope) {
        if(Type::isExpression($ast)) {
            return self::evaluate($ast, $scope);
        } else if(Type::isCons($ast)) {
            return self::evaluate($ast, $scope);
        } else if(Type::isSymbol($ast)) {
            return self::evaluate($ast, $scope);
        }
        return $ast;
    }
    
    public static function car ($node, $scope) {
        $origin = $node;
        if(Type::isLispExpression($node)) {
            if(Type::isExpression($node)) {
                $pass = $node;
                $node = ExpressionEvaluator::evaluate($node, $scope);
                if(Type::isNull($node)) {
                    return $node;
                } else if (Type::isExpression($node) || Type::isCons($node)) {
                    return $node->leftLeaf;
                } else {
                    $nodeString = self::asString($node);
                    throw new Exception("[car: 1] Error: {$nodeString} is not of type List.");
                }
            } else if (Type::isSymbol($node)) {
                $node = SymbolEvaluator::evaluate($node, $scope);
                return $node->leftLeaf;
            }
            if(Type::isNull($node)) {
                return Expression::$nilInstance;
            }
            $nodeString = self::asString($node);
            throw new Exception("[car: 2] Error: {$nodeString} is not of type List.");
        } else {
            throw new Exception("[car: 3] Error: {$node} is not of type List.");
        }
    }

    public static function warpExpression($node) {
        return new Expression(null, Type::Expression, $node, Expression::$nilInstance);
    }

    public static function cdr ($node, $scope) {
        $origin = $node;
        if(Type::isLispExpression($node)) {
            if(Type::isExpression($node)) {
                $node = ExpressionEvaluator::evaluate($node, $scope);
                if(Type::isNull($node)) {
                    return $node;
                } else if (Type::isExpression($node) || Type::isCons($node)) {
                    $cdr = $node->rightLeaf;
                } else {
                    $nodeString = self::asString($node);
                    throw new Exception("[cdr 1] Error: {$nodeString} is not of type List.");
                }
                if( Type::isCons($node)) {
                    return $cdr;
                } else if( Type::isLispExpression($cdr) ) {
                    return self::warpExpression($cdr);
                } else if ( Type::isStack($cdr) ) {
                    //現在の実装では、expressionでなければ，stackになる
                    return $cdr->toExpression();
                }
                return $node->rightLeaf;
           } else if (Type::isSymbol($node)) {
                $node = SymbolEvaluator::evaluate($node, $scope);
                if(Type::isStack($node->rightLeaf)) {
                    return $node->rightLeaf->toExpression();
                } else {
                    return $node->rightLeaf;
                }
             }
            if(Type::isNull($node)) {
                return Expression::$nilInstance;
            }
            $nodeString = self::asString($node);
            throw new Exception("[cdr 2] Error: {$nodeString} is not of type List.");
        } else {
            throw new Exception("[cdr 3] Error: {$node} is not of type List.");
        }
    }
    
    public static function asNumber($node, $scope) {
        if( Type::isExpression($node) ) {
            $node = ExpressionEvaluator::evaluate($node, $scope);
            return self::asNumber($node, $scope);
        }
        if ( Type::isSymbol($node) ) {
            $node = SymbolEvaluator::evaluate($node, $scope);
            return self::asNumber($node, $scope);
        }
        if( Type::isLispExpression($node)) {
            $node = $node->nodeValue;
        }
        if(is_numeric($node)) {
            return floatval($node);
        } else {
            throw new Exception("Error: {$node} is not of type NUMBER.");
        }
    }

    public static function tryEvalSymbol($tree, $scope) {
        if(!Type::isSymbol($tree)) {
            return $tree;
        }
        return SymbolEvaluator::evaluate($tree, $scope);
    }

    public static function tryEvalExpression($tree, $scope) {
        if(Type::isExpression($tree) || Type::isCons($tree)) {
            return ExpressionEvaluator::evaluate($tree, $scope);
        }
        return $tree;
    }

    public static function evaluate(Expression $tree, $scope) {
        switch($tree->nodeType) {
        case Type::Symbol:
            return SymbolEvaluator::evaluate($tree, $scope);
            break;
        case Type::Scalar:
            return self::asString($tree);
            break;
        case Type::Expression:
            return ExpressionEvaluator::evaluate($tree, $scope);
            break;
        case Type::Cons:
            return ExpressionEvaluator::evaluate($tree, $scope);
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
            return "not yet implemented: Expression::evaluate[lambda]";
            break;
        default:
            Debug::t($tree);
            return "not yet implemented: Expression::evaluate[default]";
            break;
        }
        return Expression::$nilInstance;
    }

    public static function reduce ($tree, $func, $init = null, $scope) {
        if( Type::isLispExpression($tree) ) {
            return self::reduceTree($tree, $func, $init, $scope);
        } else if ( Type::isStack($tree) ) {
            return self::reduceStack($tree, $func, $init, $scope);            
        }
    }

    public static function reduceStack ($stack, $func, $init = null, $scope) {
        if(isset($init)) {
            $res = $init;
        } else {
            $res = Expression::$nilInstance;
        }
        if($stack->size() > 0) {
            $rest = $stack->rest();
            $cnt = 0;
            foreach($rest as $node) {
                $res = call_user_func($func, $res, $node, $scope);
            }
        }
        return $res;
    }

    public static function reduceTree ($tree, $func, $init = null, $scope) {
        if(isset($init)) {
            $res = $init;
        } else {
            $res = Expression::$nilInstance;
        }
        return call_user_func($func, $res, $tree, $scope);
    }

    public static function map ($tree, $func, $scope) {
        if( Type::isLispExpression($tree) ) {
            return self::mapTree($tree, $func, $scope);
        } else if ( Type::isStack($tree) ) {
            return self::mapStack($tree, $func, $scope);            
        }
    }

    public static function mapStack ($stack, $func, $scope) {
        $size = $stack->size();
        while ($size --> 0) {
            $stack->push(
                call_user_func($func, $stack->shift(), $scope)
            );
        }
        return $stack;
    }

    public static function mapTree ($tree, $func, $scope) {
        $tree->leftLeaf = call_user_func($func, $tree->leftLeaf, $scope);
        if(Type::isStack($tree->rightLeaf)) {
            $tree->rightLeaf = self::mapStack($tree->rightLeaf, $func, $scope);
        } else {
            $tree->rightLeaf = call_user_func($func, $tree->rightLeaf, $scope);
        }
        return $tree;
    }
    

}

