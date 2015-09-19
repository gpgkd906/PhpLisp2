<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Environment\Environment as Environment;
use PhpLisp\Environment\SymbolTable as SymbolTable;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Expression\Stack as Stack;
use PhpLisp\Exception\ParseException as Exception;

class Parser {
    
    const Group = 100;

    static $stringTable = [];
    
    public static function initialization() {
        Reader::initialization();
        Macro::initialization();
    }
    
    public static function read($input) {
        list($sentence, $type, $raw) = Reader::scanner($input);
        if(false !== $sentence) {
            return self::parse($sentence, $type, $raw);
        }
        return false;
    }

    public static function warpSentence ($sentence) {
        return "(" . $sentence . ")";
    }

    public static function separate ($sentence) {
        //token解析しやすいため，括弧周りに空白を追加する
        $sentence = self::addDummySpace($sentence);
        $sentence = trim($sentence);
        //空白でtoken分解する、Readerが既に正規化を保証してくれるので、再正規化を行わない
        $tokens = explode(" ", $sentence);
        //シンボルに退避した文字列を復元
        //$tokens = self::restoreStringWithTokens($tokens);
        $deep = 0;
        $open = $close = null;
        $left = array();
        do {
            $token = array_shift($tokens);
            if($token === "(") {
                $deep = $deep + 1;
            }
            if($token === ")") {
                $deep = $deep - 1;
            }
            if($deep === 1 && isset($left[1]) ) {
                if($token === ")") {
                    $left[] = $token;
                } else {
                    array_unshift($tokens, $token);
                }
                $open = array_shift($left);
                $close = array_pop($tokens);
                break;
            }
            $left[] = $token;
        } while (!empty($tokens));
        if($open === null && $close === null && empty($tokens)) {
            $open = array_shift($left);
            $close = array_pop($left);
        }
        $left_repl = self::removeDummySpace(join(" ", $left));
        $right_repl = self::removeDummySpace(join(" ", $tokens));
        return array($left_repl, $right_repl, $left, $tokens);
    }
    
    public static function addDummySpace ($sentence) {
        $sentence = str_replace("(", " ( ", $sentence);
        $sentence = str_replace(")", " ) ", $sentence);
        return $sentence;
    }

    public static function removeDummySpace ($sentence) {
        while(strpos($sentence, "( ") !==false) {
            $sentence = str_replace("( ", "(", $sentence);
        }
        while(strpos($sentence, " )") !==false) {
            $sentence = str_replace(" )", ")", $sentence);
        }
        while(strpos($sentence, "  (") !==false) {
            $sentence = str_replace("  (", " (", $sentence);
        }
        while(strpos($sentence, ")  ") !==false) {
            $sentence = str_replace(")  ", ") ", $sentence);
        }
        while(strpos($sentence, "  ") !==false) {
            $sentence = str_replace("  ", " ", $sentence);
        }
        return trim($sentence);
    }

    public static function replaceStringWithSymbol($sentence) {
        $offset = 0;
        $isString = false;
        $inString = -1;
        if(empty($sentence)) {
            return $sentence;
        }
        while(false !== ($offset = strpos($sentence, '"', $offset + 1))) {
            if($offset === 0) {
                continue;
            }
            if($sentence[$offset - 1] === "\\") {
                continue;
            }
            $isString = ! $isString;

            if($isString) {
                $inString = $offset;
            }
            if(!$isString) {
                $symbolId = Environment::generateUniqueId("StringSymbol");
                $string = self::subString($sentence, $inString, $offset + 1);
                Environment::setSymbol(Environment::$rootScope, $symbolId, $string);
                $sentence = substr_replace($sentence, $symbolId, $inString, $offset + 1 - $inString);
                $offset = 0;
            }
        }
        return $sentence;
    }

    static public function restoreStringWithTokens($tokens) {
        foreach($tokens as $key => $token) {
            if(isset(self::$stringTable[$token])) {
                $tokens[$key] = self::$stringTable[$token];
            }
        }
        return $tokens;
    }

    static public function subString($string, $index, $offset = -1)
    {
        if($offset === -1) {
            $length = strlen($string) - $offset;
        } else {
            $length = $offset - $index;
        }
        return substr($string, $index, $length);
    }
    
    public static function parse ($sentence, $type, $raw) {
        switch($type) {
        case self::Group:
            $stack = new Stack;
            do {
                $sentence = self::warpSentence($sentence);
                list($left, $right) = self::separate($sentence);
                $stack->push( self::read($left) );
                $sentence = Reader::normalize($right);
                if($sentence === "()") {
                    $stack->push( Expression::$nilInstance );
                    $sentence = "";
                }
            } while (isset($sentence[0]));
            $node = $stack;
            break;
        case Type::Nil:
            $node = Expression::$nilInstance;            
            break;
        case Type::Expression:
            list($sentence_left, $sentence_right, $tokens_left, $tokens_right) = self::separate($sentence);
            $sentence_left = Reader::normalize($sentence_left);
            $sentence_right = Reader::normalize($sentence_right);
            $node = new Expression(
                $raw,
                $type,
                self::read($sentence_left),
                self::read($sentence_right) ?: Expression::$nilInstance
            );
            $node = Transform::translate($node, $sentence, $sentence_left, $sentence_right);
            $node = Macro::expand($node);
            break;
        default:
            $node = new Expression($raw, $type);
            break;
        }
        return $node;
    }

}
