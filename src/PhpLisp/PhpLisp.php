<?php
namespace PhpLisp;

use PhpLisp\Parser\Parser as Parser;
use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Exception\Exception as Exception;

class PhpLisp {
   
    public static function initialization () {
        //PhpLisp 実行環境初期化（VM初期化）
        Environment::initialization();
        //Parser初期化
        Parser::initialization();
    }

    /**
     * 会話式インタフェース
     */
    public static function repl () {
        try {
            while($input = self::readline("PhpLisp:>")) {
                if( $input === "exit") {
                    break;
                }
                while(!$ast = Parser::read($input)) {
                    $input = self::readline("PhpLisp:>");
                }
                $result = Evaluator::evalTree($ast, Environment::$rootScope);
                self::write($result);
                self::write(Environment::$eol);
            }
            echo PHP_EOL;
        } catch (Exception $e) {
            self::write($e->getOriginMessage());
            self::write(Environment::$eol);
            self::repl();
        }
    }

    /**
     * インタプリターインタフェース
     */
    public static function interpreter ($file) {
        //以下はパス処理とlispファイル読み込み・実行
        if(!is_file($file)) {
            self::write("{$file} not found!");
            exit();
        }
        Environment::$stdin = fopen($file, "r");
        try {
            while($input = self::read()) {
                while(!$ast = Parser::read($input)) {
                    $input = self::read();
                }
                $result = Evaluator::evalTree($ast, Environment::$rootScope);
                self::write($result);
                self::write(Environment::$eol);
            }
        } catch (Exception $e) {
            self::write($e->getOriginMessage());
            self::write(Environment::$eol);
        }
    }
   
    /**
     * コンパイラーインタフェース
     * 未実装(現時点では実装予定なし)
     */
    private static function compile () {}
    
    public static function readline($prompt) {
        if(extension_loaded('readline')) {
            $input = readline($prompt);
            if(isset($input[0])) {
                readline_add_history($input);
            }
            return $input;
        } else {
            self::write($prompt);
            return self::read();
        }
    }

    public static function read () {
        return Environment::read();
    }
    
    public static function write ($output) {
        return Environment::write($output);
    }
    
}