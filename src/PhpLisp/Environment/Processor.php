<?php

namespace PhpLisp\Environment;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Parser\Parser as Parser;
use PhpLisp\Exception\EvalException as Exception;

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
            "assert" => new Expression("exit", Type::Func, null, function($tree, $node, $scope) {
                $left = Evaluator::car($tree, $scope);
                $right = Evaluator::cdr($tree, $scope);
                if(Evaluator::evalTree($left) === Evaluator::evalTree($right)) {
                    return true;
                } else {
                    throw new Exception("assert failed!{Evaluator::asString($tree)}");
                }
            }),
            "exit" => new Expression("exit", Type::Func, null, function($tree, $node, $scope) {
                exit();
            }),
            "debug" => new Expression("debug", Type::Func, null, function($tree, $node, $scope) {
                Debug::$mode = !Type::isNull($tree);
            }),
            "__add__" => new Expression("__add__", Type::Func, null, function($tree, $node, $scope) {
                return Evaluator::asNumber(Evaluator::car($tree, $scope), $scope) + Evaluator::asNumber(Evaluator::car(Evaluator::cdr($tree, $scope), $scope), $scope);
            }),
            "+" => new Expression("+", Type::Func, null, function($tree, $node, $scope) {
                return Evaluator::reduce($tree, function($sum, $node, $scope) {
                    return $sum + Evaluator::asNumber($node, $scope);
                }, 0, $scope);
            }),
            "-" => new Expression("-", Type::Func, null, function($tree, $node, $scope) {
                return Evaluator::reduce(Evaluator::cdr($tree, $scope), function($sum, $node, $scope) {
                    return $sum - Evaluator::asNumber($node, $scope);
                }, Evaluator::asNumber(Evaluator::car($tree, $scope), $scope), $scope);
            }),
            "*" => new Expression("*", Type::Func, null, function($tree, $node, $scope) {
                return Evaluator::reduce($tree, function($res, $node, $scope) {
                    return $res * Evaluator::asNumber($node, $scope);
                }, 1, $scope);
            }),
            "/" => new Expression("/", Type::Func, null, function($tree, $node, $scope) {
                return Evaluator::reduce(Evaluator::cdr($tree, $scope), function($res, $node, $scope) {
                    return $res / Evaluator::asNumber($node, $scope);
                }, Evaluator::asNumber(Evaluator::car($tree, $scope), $scope), $scope);
            }),
            "progn" => new Expression("progn", Type::Func, null, function($tree, $node, $scope) {
                
            }),
            "defun" => new Expression("defun", Type::Func, null, function ($tree, $node, $scope) {
                $symbol = Evaluator::car($tree, $scope);
                if( !Type::isSymbol($symbol) ) {
                    throw new Exception("Error: {Evaluator::asString($symbol)} is not of type SYMBOL.");
                }
                $node = Evaluator::cdr($tree, $scope);
                $funcParam = Evaluator::car($node, $scope);
                $funcBody = Evaluator::cdr($node, $scope);
                $nodeValue = "(LAMBDA-BLOCK " . Evaluator::asString($symbol) . " " . Evaluator::asString($node) . ")";
                $lambda = new Expression($nodeValue, Type::Lambda, $funcParam, $funcBody);
                return Environment::setLambda($scope, Evaluator::asString($symbol), $lambda);
            }),
            "setf" => new Expression("setf", Type::Func, null, function ($tree, $node, $scope) {
                if(Type::isScalar($tree)) {
                    throw new Exception("Error: No value for {Evaluator::asString($tree)}.");
                }
                $symbol = Evaluator::car($tree, $scope);
                if(!Type::isSymbol($symbol)) {
                    throw new Exception("Error: {Evaluator::asString($symbol)} is not of type SYMBOL.");
                }
                $body = Evaluator::evaluate(Evaluator::cdr($tree, $scope), $scope);
                $body = Evaluator::tryEvalExpression($body, $scope);
                return Environment::setSymbol($scope, Evaluator::asString($symbol), $body);
            }),
            "not" => new Expression("not", Type::Func, "not", function ($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                if(Type::isNull($tree)) {
                    return Expression::$trueInstance;
                }
                return Expression::$nilInstance;
            }),
            "car" => new Expression("car", Type::Func, "car", function ($tree, $node, $scope) {
                return Evaluator::car(Evaluator::tryEvalExpression($tree, $scope), $scope);
            }),
            "cdr" => new Expression("cdr", Type::Func, "cdr", function ($tree, $node, $scope) {
                return Evaluator::cdr(Evaluator::tryEvalExpression($tree, $scope), $scope);
            }),
            "atom" => new Expression("atom", Type::Func, null, function ($tree, $node, $scope) {
                $tree = Evaluator::tryEvalExpression($tree, $scope);
                return Type::isQuote($tree) ? Expression::$nilInstance : Expression::$trueInstance;
            }),
            "eql" => new Expression("eql", Type::Func, null, function ($tree, $node, $scope) {
                $left = Evaluator::asRaw(Evaluator::tryEvalExpression(Evaluator::car($tree, $scope), $scope));
                $right = Evaluator::asRaw(Evaluator::tryEvalExpression(Evaluator::car(Evaluator::cdr($tree, $scope), $scope), $scope));
                return $left === $right ? Expression::$trueInstance : Expression::$nilInstance;
            }),
            "list" => new Expression("list", Type::Func, null, function ($tree, $node, $scope) {
                return Environment::write("not yet implemented");
            }),
            "cons" => new Expression("cons", Type::Func, null, function ($tree, $node, $scope) {
                $tree = Evaluator::unpackQuote($tree);
                $left = $tree->leftLeaf;
                $right = $tree->rightLeaf;
                if(Type::isExpression($left)) {
                    $left = Evaluator::quote($left);
                }
                $right = Evaluator::tryEvalExpression($right, $scope);
                if (Type::isQuote($right)) {
                    $rightValue = Evaluator::asString(Evaluator::unpackQuote($right));
                    $nodeValue = Evaluator::asString($left) . " ". $rightValue;
                    $node = new Expression($nodeValue, Type::Expression, $left, $right);
                } else if (Type::isNull($right)){
                    $node = $left;
                } else {
                    $nodeValue = Evaluator::asString($left) . " . " . Evaluator::asString($right);
                    $node = new Expression($nodeValue, Type::Cons, $left, $right);
                }
                return Evaluator::quote($node);
            }),
            "cond" => new Expression("cond", Type::Func, null, function ($tree, $node, $scope) {
                $res = Expression::$nilInstance;
                while(!Type::isNull($tree)) {
                    $left = Evaluator::car($tree, $scope);
                    $right = Evaluator::cdr($tree, $scope);
                    $rest = Evaluator::cdr($right, $scope);
                    if(!Type::isExpression($left) && !Type::isNull($rest)) {
                        $nodeString = Evaluator::asString($left);
                        throw new Exception("Error: {$nodeString} is an illegal COND clause.");
                    }
                    if(Type::isNull($rest) && Type::isNull($right)) {
                        $res = $left;
                        break;
                    }
                    if(Type::isSymbol(Evaluator::car($left, $scope))) {
                        $test = Evaluator::evaluate($left, $scope);
                        $tmp = Evaluator::car(Evaluator::car($right, $scope), $scope);
                        $tree = Evaluator::cdr($right, $scope);
                    } else {
                        $test = Evaluator::car($left, $scope);
                        $tmp = Evaluator::car(Evaluator::cdr($left, $scope), $scope);
                        $tree = $right;
                    }
                    if(Type::isTrue($test)) {
                        $res = $tmp;
                        break;
                    }
                }
                return $res;
            })
        );
    }

    public static function get() {
        return self::$operators;
    }
}