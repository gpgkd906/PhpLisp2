<?php

namespace PhpLisp\Environment;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\EvalException as Exception;

class Environment {
    public static $symbolTable;
    public static $lambdaTable;
    public static $stdin;
    public static $stdout;
    public static $eol;
    public static $rootScope;
    private static $seed;
    private static $terminalCode;

    //VMの実行環境を初期化
    public static function initialization() {
        self::$stdin = STDIN;
        self::$stdout = STDOUT;
        self::$eol = PHP_EOL;
        self::$seed = time();
        self::$rootScope = "root";
        self::$terminalCode = array("exit", false);

        if(!empty(self::$symbolTable)) {
            return false;
        }
        //Processorを初期化 (buildin function)
        Processor::initialization();
        //SymbolTable初期化
        self::$symbolTable = new SymbolTable;
        self::$lambdaTable = new SymbolTable;
        foreach(Processor::get() as $symbol => $node) {
            if(Type::isFunc($node) || Type::isLambda($node)) {
                self::setLambda(self::$rootScope, $symbol, $node);
            } else {
                self::setSymbol(self::$rootScope, $symbol, $node);
            }
        }
        //nilやTのS式初期化(nil/Tは内部的に繰り返すで利用するため)
        Expression::$nilInstance = new Expression("Nil", Type::Nil);
        Expression::$trueInstance = new Expression("T", Type::True);
    }
    
    public static function setSymbol($scope, $symbol, $node) {
        if(!$symbolTable = self::$symbolTable->get($scope)) {
            $symbolTable = new SymbolTable;
            self::$symbolTable->set($scope, $symbolTable);
        }
        return $symbolTable->set($symbol, $node);
    }

    public static function getSymbol($scope, $symbol) {
        if(!$symbolTable = self::$symbolTable->get($scope)) {
            return false;
        }
        if(!$target = $symbolTable->get($symbol)) {
            return false;
        }
        return $target;
    }

    public static function displaySymbol($scope) {
        if(!$target = self::$symbolTable->get($scope)) {
            $target = new SymbolTable;
            self::$symbolTable->set($scope, $target);
        }
        $target->showAll();
    }

    public static function setLambda($scope, $symbol, $node) {
        if(!$symbolTable = self::$lambdaTable->get($scope)) {
            $symbolTable = new SymbolTable;
            self::$lambdaTable->set($scope, $symbolTable);
        }
        return $symbolTable->set($symbol, $node);
    }

    public static function getLambda($scope, $symbol) {
        if(!$symbolTable = self::$lambdaTable->get($scope)) {
            $symbolTable = self::$lambdaTable->get(self::$rootScope);
        }
        if(!$target = $symbolTable->get($symbol)) {
            return false;
        }
        return $target;
    }
    
    public static function displayLambda($scope) {
        if(!$target = self::$lambdaTable->get($scope)) {
            $target = new SymbolTable;
            self::$lambdaTable->set($scope, $target);
        }
        $target->showAll();
    }
    
    public static function generateUniqueId($prefix) {
        if(empty($prefix)) {
            throw new Exception("internal Error! Environment::generateUniqueId must get [prefix] argument");
        }
        self::$seed = self::$seed + 1;
        return $prefix . self::$seed;
    }
    
    public static function read() {
        $input = false;
        while(!$input) {
            if(feof(self::$stdin)) {
                self::terminal();
            }
            //空行なら読み飛ばし
            $input = fgets(self::$stdin);            
            $input = str_replace(PHP_EOL, "", trim(strtolower($input)));
        }
        $input = trim($input);
        if(empty($input)) {
            return false;
        }
        if(in_array($input, self::$terminalCode)) {
            self::terminal();
        }
        return $input;
    }
    
    public static function write($evalResult) {
        fwrite(self::$stdout, $evalResult);
    }

    public static function terminal() {
        die();
    }

}

