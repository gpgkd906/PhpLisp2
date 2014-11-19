<?php
namespace PhpLisp;

use PhpLisp\Parser\Parser as Parser;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Exception\Exception as Exception;

class PhpLisp {
   
    public static function initialization() {
        //Parser初期化
        Parser::initialization();
        //PhpLisp VM初期化
        Environment::initialization();
    }

    public static function script($file) {
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
    
    public static function repl() {
        try {
            self::write("PhpLisp:>");
            while($input = self::read()) {
                while(!$ast = Parser::read($input)) {
                    self::write("PhpLisp:>");
                    $input = self::read();
                }
                $result = Evaluator::evalTree($ast, Environment::$rootScope);
                self::write($result);
                self::write(Environment::$eol);
                self::write("PhpLisp:>");
            }
            echo PHP_EOL;
        } catch (Exception $e) {
            self::write($e->getOriginMessage());
            self::write(Environment::$eol);
            self::repl();
        }
    }

    public static function read() {
        return Environment::read();
    }

    public static function write($output) {
        return Environment::write($output);
    }
    
}