<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Environment\Stack as Stack;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Evaluator\Evaluator as Evaluator;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\ParseException as Exception;

class Parser {
    
    const Group = 100;
    
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

    public static function flushError($errorMessage) {
        throw new Exception($errorMessage);
    }

    public static function warpSentence ($sentence) {
        return "(" . $sentence . ")";
    }

    public static function separate ($sentence) {
        //token解析しやすいため，括弧周りに空白を追加する
        $sentence = self::addDummySpace($sentence);
        $sentence = trim($sentence);
        //空白でtoken分解する、ここはReaderが正規化を保証してくれると仮定するので、再正規化を行わない
        //Readerの正規化にも重複な空白は取り除いたので，そのまま分解する        
        $tokens = explode(" ", $sentence);
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
        return array($left_repl, $right_repl);
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

    private static function parse ($sentence, $type, $raw) {
        if($type == self::Group) {
            $stack = new Stack;
            do {
                $sentence = self::warpSentence($sentence);
                list($left, $right) = self::separate($sentence);
                $stack->push( self::read($left) );
                $sentence = trim($right);                
            } while (isset($sentence[0]));
            return $stack;
        }
        if($type === Type::Nil) {
            return Expression::$nilInstance;
        }
        if($sentence !== "0" && empty($sentence)) {
            return Expression::$nilInstance;
        }
        $node = new Expression;
        $node->setType($type);
        $node->nodeValue = $sentence;
        $node->rawValue = $raw;
        switch($type) {
        case Type::Expression:
            list($sentence_left, $sentence_right) = self::separate($sentence);
            $node->leftLeaf = self::read($sentence_left);
            $node->rightLeaf = self::read($sentence_right) ?: Expression::$nilInstance;
            break;
        }
        return $node;
    }

}
