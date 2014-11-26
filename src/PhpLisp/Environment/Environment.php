<?php

namespace PhpLisp\Environment;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\EvalException as Exception;
use PhpLisp\Operator\Operator as Operator;

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
        self::$rootScope = array("phplist-user");
        self::$terminalCode = array("exit", false);

        //初期化後、$symbolTableが必ず存在するので
        //$symbolTableをチェックすることで、重複初期化を避ける
        if(!empty(self::$symbolTable)) {
            return false;
        }
        //SymbolTable初期化
        self::$symbolTable = new SymbolTable;
        self::$lambdaTable = new SymbolTable;
        //buildin関数を登録する
        foreach(Operator::getAll() as $symbol => $node) {
            self::setLambda(self::$rootScope, $symbol, $node);
        }
        //nilやTのS式初期化(nil/Tは内部的に繰り返すで利用するためキャッシュする)
        Expression::$nilInstance = new Expression("Nil", Type::Nil);
        Expression::$trueInstance = new Expression("T", Type::True);
        //quoteやlistのSymbolもマクロ変換で繰り返すで利用されるためキャッシュする
        Expression::$quoteInstance = new Expression("quote", Type::Symbol);
        Expression::$listInstance = new Expression("list", Type::Symbol);
    }

    public static function setSymbol($scopeChain, $symbol, $node) {
        //パラメタの約束は常にもっとも内側のスコープに約束する
        $scope = array_pop($scopeChain);
        if(!$symbolTable = self::$symbolTable->get($scope)) {
            $symbolTable = new SymbolTable;
            self::$symbolTable->set($scope, $symbolTable);
        }
        return $symbolTable->set($symbol, $node);
    }

    public static function getSymbol($scopeChain, $symbol) {
        //パラメタのアクセスは内側から外側に辿りついて行く
        while($scope = array_pop($scopeChain)) {
            if(!$symbolTable = self::$symbolTable->get($scope)) {
                continue;
            }
            if(($target = $symbolTable->get($symbol)) === null) {
                continue;
            }
            return $target;
        }
        return null;
    }

    public static function displaySymbol($scopeChain) {
        //display時基本的にコールされる場所のスコープを見るので、もっとも内側
        $scope = array_pop($scopeChain);
        if(!$target = self::$symbolTable->get($scope)) {
            $target = new SymbolTable;
            self::$symbolTable->set($scope, $target);
        }
        $target->showAll();
    }

    public static function setLambda($scopeChain, $symbol, $node) {
        //ラムダの約束は常にもっとも内側のスコープに約束する
        $scope = array_pop($scopeChain);
        if(!$symbolTable = self::$lambdaTable->get($scope)) {
            $symbolTable = new SymbolTable;
            self::$lambdaTable->set($scope, $symbolTable);
        }
        return $symbolTable->set($symbol, $node);
    }

    public static function getLambda($scopeChain, $symbol) {
        //ラムダのアクセスは内側から外側に辿りついて行く
        while($scope = array_pop($scopeChain)) {
            if(!$symbolTable = self::$lambdaTable->get($scope)) {
                continue;
            }
            if(($target = $symbolTable->get($symbol)) === null) {
                continue;
            }
            return $target;
        }
        return null;
    }
    
    public static function displayLambda($scopeChain) {
        //display時基本的にコールされる場所のスコープを見るので、もっとも内側
        $scope = array_pop($scopeChain);
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
    
    public static function write($result) {
        if(Type::isLispExpression($result)) {
            $result = Evaluator::asString($result); 
        }
        fwrite(self::$stdout, $result);
    }

    public static function terminal() {
        die();
    }

}

