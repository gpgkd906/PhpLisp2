<?php

namespace PhpLisp\Environment;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Parser\Parser as Parser;
use PhpLisp\Exception\EvalException as Exception;

use PhpLisp\Evaluator\ExpressionEvaluator as ExpressionEvaluator;

class Processor {
    private static $operators;
       
    public static function initialization () {
        self::$operators = array(
            "open" => new Expression("open", Type::Func, null, function($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                if(Type::isScalar($tree)) {
                    $filename = $tree->nodeValue;
                    $mode = "rw";
                } else {
                    $filename = Evaluator::asRaw(Evaluator::car($tree, $scope));
                    $mode = Evaluator::asRaw(Evaluator::car(Evaluator::cdr($tree, $scope), $scope));
                }
                if(!is_file($filename)) {
                    throw new Exception("Error: Cannot open the file {$filename}.");
                }
                $handler = fopen($filename, $mode);
                return new Expression($handler, Type::Stream, null, null);
            }),
            "close" => new Expression("close", Type::Func, null, function($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                $resource = Evaluator::asRaw(Evaluator::evaluate($tree, $scope));
                if(!is_resource($resource)) {
                    throw new Exception("Error: {$symbol} is invalid resource.");                    
                }
                fclose($resource);
                return Expression::$trueInstance;
            }),
            "read-line" => new Expression("read-line", Type::Func, null, function($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                $resource = Evaluator::asRaw(Evaluator::evaluate($tree, $scope));
                if(!is_resource($resource)) {
                    throw new Exception("Error: {$symbol} is invalid resource.");                    
                }
                $line = fgets($resource);
                if(feof($resource)) {
                    return Expression::$nilInstance;
                } else {
                    return new Expression($line, Type::Scalar);
                }
            }),
            "help" => new Expression("help", Type::Func, null, function($tree, $node, $scope) {
                //マグロシステム実装するまで，lisp上while/loopを使えない
                //なので，ここはとにかくPHPの実装にする
                Environment::write(Environment::$eol);
                Environment::write(file_get_contents("doc/HELP"));
                Environment::write(Environment::$eol);
            }),
            "dump" => new Expression("dump", Type::Func, null, function($tree, $node, $scope) {
                if(Type::isSymbol($tree)) {
                    if($node = Environment::getSymbol($scope, Evaluator::asString($tree))) {
                        return Debug::t($node);
                    } else if($node = Environment::getLambda($scope, Evaluator::asString($tree))) {
                        return Debug::t($node);
                    }
                }
                return Debug::t($tree);
            }),
            "assert" => new Expression("assert", Type::Func, null, function($tree, $node, $scope) {
                $left = Evaluator::car($tree, $scope);
                $right = Evaluator::cdr($tree, $scope);
                if(Evaluator::evalTree($left) === Evaluator::evalTree($right)) {
                    return Expression::$trueInstance;
                } else {
                    throw new Exception("assert failed!{Evaluator::asString($tree)}");
                }
            }),
            "exit" => new Expression("exit", Type::Func, null, function($tree, $node, $scope) {
                Environment::write("exit!");
                exit();
            }),
            "debug" => new Expression("debug", Type::Func, null, function($tree, $node, $scope) {
                Debug::$mode = !Type::isNull($tree);
            }),
            "quote" => new Expression("quote", Type::Func, null, function($tree, $node, $scope) {
                return $tree;
            }),
            "+" => new Expression("+", Type::Func, null, function($tree, $node, $scope) {
                Debug::$mode = true;
                return Evaluator::reduce($tree, function($res, $node, $scope) {
                    if(Type::isNull($res)) {
                        return $node;
                    }
                    return new Expression(Evaluator::asNumber($res, $scope) + Evaluator::asNumber($node, $scope), Type::Scalar);
                }, null, $scope);
            }),
            "-" => new Expression("-", Type::Func, null, function($tree, $node, $scope) {
                $result = Evaluator::reduce($tree, function($res, $node, $scope) {
                    if(Type::isNull($res)) {
                        return $node;
                    }
                    return new Expression(Evaluator::asNumber($res, $scope) - Evaluator::asNumber($node, $scope), Type::Scalar);
                }, null, $scope);
                return $result;
            }),
            "*" => new Expression("*", Type::Func, null, function($tree, $node, $scope) {
                return Evaluator::reduce($tree, function($res, $node, $scope) {
                    if(Type::isNull($res)) {
                        return $node;
                    }
                    return new Expression(Evaluator::asNumber($res, $scope) * Evaluator::asNumber($node, $scope), Type::Scalar);
                }, null, $scope);
            }),
            "/" => new Expression("/", Type::Func, null, function($tree, $node, $scope) {
                //Debug::p($tree);
                return Evaluator::reduce($tree, function($res, $node, $scope) {
                    if(Type::isNull($res)) {
                        return $node;
                    }
                    return new Expression(Evaluator::asNumber($res, $scope) / Evaluator::asNumber($node, $scope), Type::Scalar);
                }, null, $scope);
            }),
            "progn" => new Expression("progn", Type::Func, null, function($tree, $node, $scope) {
                
            }),
            "lambda" => new Expression("lambda", Type::Func, null, function($stack, $node, $scope) {
                $lambdaHeader = "LAMBDA-CLOSURE () () ()";
                $lambdaParam = Expression::$nilInstance;
                $lambdaBody = Expression::$nilInstance;
                if( !Type::isStack($stack) ) {
                    if( Type::isNull($stack) ) {
                        $nodeValue = "(" . $lambdaHeader . ")";
                    } else {
                        $nodeValue = "(" . $lambdaHeader . " " . Evaluator::asString($stack) . ")";
                        $lambdaParam = Stack::fromExpression($stack);
                    }
                } else {
                    $nodeValue = str_ireplace("lambda", $lambdaHeader, Evaluator::asString($node));
                    $lambdaParam = Stack::fromExpression($stack->shift());
                    $lambdaBody = $stack;
                }
                return new Expression($nodeValue, Type::Lambda, $lambdaParam, $lambdaBody);
            }),
            "defun" => new Expression("defun", Type::Func, null, function ($stack, $node, $scope) {
                if( !Type::isStack($stack) ) {
                    throw new Exception("Error: Too few arguments.");
                }
                if( $stack->size() < 2 ) {
                    throw new Exception("Error: Too few arguments.");
                }
                $symbol = $stack->shift();
                if( !Type::isSymbol($symbol) ) {
                    throw new Exception("Error: {Evaluator::asString($symbol)} is not of type SYMBOL.");
                }
                $lambdaParam = Stack::fromExpression($stack->shift());
                $lambdaBody = $stack;
                $nodeValue = str_ireplace("defun", "LAMBDA-BLOCK", Evaluator::asString($node) );
                $lambda = new Expression($nodeValue, Type::Lambda, $lambdaParam, $lambdaBody);
                return Environment::setLambda($scope, Evaluator::asString($symbol), $lambda);
            }),
            "setf" => new Expression("setf", Type::Func, null, function ($stack, $node, $scope) {
                if(Type::isNull($stack)) {
                    return Expression::$nilInstance;
                } else if (Type::isStack($stack)) {
                    $stackSize = $stack->size();
                    if($stackSize === 0) {
                        return Expression::$nilInstance;
                    }
                    if(($stackSize % 2) !== 0) {
                        $lastUnit = $stack->pop();
                        $stack->push($lastUnit);
                        $nodeString = Evaluator::asString($lastUnit);
                        throw new Exception("Error: No value for {$nodeString}.");
                    }
                    $offset = 0;

                    while($offset < $stackSize) {
                        $symbol = $stack->getAt($offset);
                        $value = $stack->getAt($offset + 1);
                        if( !Type::isSymbol($symbol) ) {
                            $nodeString = Evaluator::asString($symbol);
                            throw new Exception("Error: {$nodeString} is not of type SYMBOL.");
                        }
                        $symbolKey = Evaluator::asString($symbol);
                        $value = Evaluator::tryEvalExpression($value, $scope);
                        Environment::setSymbol($scope, $symbolKey, $value);
                        $offset = $offset + 2;
                    }
                    return $value;
                }
                $nodeString = Evaluator::asString($stack);
                throw new Exception("Error: No value for {$nodeString}.");
            }),
            "not" => new Expression("not", Type::Func, "not", function ($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                if(Type::isNull($tree)) {
                    return Expression::$trueInstance;
                }
                return Expression::$nilInstance;
            }),
            "car" => new Expression("car", Type::Func, "car", function ($tree, $node, $scope) {
                return Evaluator::car($tree, $scope);
            }),
            "cdr" => new Expression("cdr", Type::Func, "cdr", function ($tree, $node, $scope) {
                return Evaluator::cdr($tree, $scope);
            }),
            "atom" => new Expression("atom", Type::Func, null, function ($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                return Type::isQuote($tree) ? Expression::$nilInstance : Expression::$trueInstance;
            }),
            "eql" => new Expression("eql", Type::Func, null, function ($stack, $node, $scope) {
                if(!Type::isStack($stack)) {
                    if(Type::isNull($stack)) {
                        throw new Exception("Error: CONS [or a callee] requires more than 0 arguments.");
                    } else {
                        throw new Exception("Error: CONS [or a callee] requires more than 1 arguments.");
                    }
                }
                $stackSize = $stack->size();
                if($stackSize > 2) {
                    throw new Exception("Error: CONS [or a callee] requires less than {$stackSize} arguments.");
                }
                $left = $stack->getAt(0);
                $right = $stack->getAt(1);
                $left = Evaluator::tryEvalSymbol($left, $scope);
                $left = Evaluator::tryEvalExpression($left, $scope);
                $right = Evaluator::tryEvalExpression($right, $scope);
                //型チェック
                if($left->nodeType !== $right->nodeType) {
                    return Expression::$nilInstance;
                }
                //値チェック
                $left = (string) $left->nodeValue;
                $right = (string) $right->nodeValue;
                if($left === $right) {
                    return Expression::$trueInstance;
                } else {
                    return Expression::$nilInstance;
                }
            }),
            "list" => new Expression("list", Type::Func, null, function ($tree, $node, $scope) {
                return Environment::write("not yet implemented");
            }),
            "cons" => new Expression("cons", Type::Func, null, function ($stack, $node, $scope) {
                if(!Type::isStack($stack)) {
                    if(Type::isNull($stack)) {
                        throw new Exception("Error: CONS [or a callee] requires more than 0 arguments.");
                    } else {
                        throw new Exception("Error: CONS [or a callee] requires more than 1 arguments.");
                    }
                }
                $stackSize = $stack->size();
                if($stackSize > 2) {
                    throw new Exception("Error: CONS [or a callee] requires less than {$stackSize} arguments.");
                }
                $left = $stack->getAt(0);
                $right = $stack->getAt(1);
                $left = Evaluator::tryEvalExpression($left, $scope);
                $right = Evaluator::tryEvalExpression($right, $scope);
                if(Type::isExpression($right)) {
                    $nodeValue = substr_replace(Evaluator::asString($right), "(" . Evaluator::asString($left) . " ", 0, 1);
                    $cons = new Expression($nodeValue, Type::Expression, $left, Stack::fromExpression($right));
                } else if(Type::isNull($right)) {
                    $nodeValue = "(" . Evaluator::asString($left) . ")";
                    $cons = new Expression($nodeValue, Type::Cons, $left, $right);                    
                } else {
                    $nodeValue = "(" . Evaluator::asString($left) . " . " . Evaluator::asString($right) . ")";
                    $cons = new Expression($nodeValue, Type::Cons, $left, $right);
                }
                return $cons;
            }),
            "cond" => new Expression("cond", Type::Func, null, function ($stack, $node, $scope) {
                $res = Expression::$nilInstance;
                if( !Type::isStack($stack) ) {
                    if(Type::isNull($stack)) {
                        return $res;
                    }
                    $tmp = new Stack;
                    $tmp->push($stack);
                    $stack = $tmp;
                }
                $stackSize = $stack->size();
                $offset = 0;
                while($offset < $stackSize) {
                    $unit = $stack->getAt($offset);
                    $offset = $offset + 1;
                    $left = $unit->leftLeaf;
                    $right = $unit->rightLeaf;
                    
                    if(Type::isExpression($left)) {
                        $left = ExpressionEvaluator::evaluate($left, $scope);
                    }
                    if(Type::isNull($left)) {
                        continue;
                    }
                    if(Type::isNull($right)) {
                        if( Type::isCons($unit) ) {
                            $res = $left;
                        } else {
                            $res = $right;
                        }
                    } else if(Type::isExpression($right)) {
                        $res = ExpressionEvaluator::evaluate($right, $scope);
                    } else {
                        $res = $right;
                    }
                    break;
                }
                return $res;
            })
        );
    }

    public static function get() {
        return self::$operators;
    }
}