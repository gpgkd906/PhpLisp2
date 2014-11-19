<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\ParseException as Exception;

class Reader {
    
    public static $sentenceStack;
    
    public static function initialization() {
        self::$sentenceStack = array();
    }
    
    public static function isNilSentence ($sentence) {
        $isNil = (strtoupper($sentence) === strtoupper(Expression::$nilInstance->nodeValue));
        $isEmpty = (str_replace(" ", "", $sentence) === "()");
        return $isNil || $isEmpty;
    }

    public static function isTrueSentence ($sentence) {
        return (strtoupper($sentence) === strtoupper(Expression::$trueInstance->nodeValue));
    }

    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function isStringSentence ($sentence) {
        if(substr_count($sentence, '"') === 2) {
            if(substr($sentence, 0, 1) === '"') {
                if(substr($sentence, -1, 1) === '"') {
                    return true;
                }
            }
        }
        return false;
    }
 
    /**
     * 格式上有効なS式のチェック
     * ここは格式上のチェックであるので，論理チェックではありません
     *  ※成功例: '(1 2 3)、論理上有効であるし、格式上有効である
     *  ※成功例: (1 2 3)、論理上有効ではないが、格式上有効である
     *  ※失敗例: 1 2 3、論理上有効ではないし、格式上有効でもない
     *  ※失敗例: (1 2) (3 4) (5 6) 論理上有効ではないし、格式上有効でもない
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function isExpressionSentence ($sentence) {
        //括弧がないとS式ではない
        if(substr_count($sentence, "(") === 0) {
            return false;
        }
        //先頭が ( か '(でないとS式ではない
        if(substr($sentence, 0, 1) !== "(" && substr($sentence, 0, 2) !== "'(" ) {
            return false;
        }
        //末尾が ) でないとS式ではない
        if(substr($sentence, -1, 1) !== ")") {
            return false;
        }
        // (1 2 3) (2 3 4)のようなS式が並んでも、全体としてはS式ではない
        $in = 0;
        $length = strlen($sentence);
        $lastOffset = $length - 1;
        $offset = strpos($sentence, "(");
        do {
            $char = $sentence[$offset];
            if($char === "(") {
                $in = $in + 1;
            }
            if($char === ")") {
                $in = $in - 1;
            }
            if($in === 0) {
                break;
            }
        } while( ++ $offset < $length);
        if($offset < $lastOffset) {
            return false;
        }
        return true;
    }

    public static function isScalarSentence ($sentence) {
        //lispにおいてのスカラかどかを調べます ※つまり数値スカラか文字列スカラの判定
        $isString = self::isStringSentence($sentence);
        $isNumeric = is_numeric($sentence);
        return $isNumeric || $isString;
    }

    public static function isSymbolSentence ($sentence) {
        // 空白はtoken解析用
        if(strpos($sentence, " ") !== false) {
            return false;
        }
        //:はパッケージ用 (パッケージは実装予定がありませんが)
        if(strpos($sentence, ":") !== false) {
            return false;
        }
        //それ以外の場合はシンボル用に使える
        return true;
    }

    public static function trimSingleComment ($line) {
        $offset = strpos($line, ";");
        if($offset !== false) {
            $line = substr_replace($line, "", $offset);
        }
        return $line;
    }
    
    public static function trimMultiComment ($line) {
        $openTag = "#|";
        $closeTag = "|#";
        $closeTagLength = strlen($closeTag);
        $startOffset = null;
        $endOffset = null;
        while(($offset = strpos($line, $openTag)) !== false) {
            $startOffset = $offset;
            if(($endOffset = strpos($line, $closeTag)) === false) {
                throw new Exception("invalid S-expression, Comment Tag unmatch!");
            }
            $commentLength = $endOffset + $closeTagLength - $startOffset;
            $line = substr_replace($line, "", $startOffset, $commentLength);
            $startOffset = null;
            $endOffset = null;
        }
        if($startOffset !== null && $endOffset === null) {
            $line = substr_replace($line, "", $startOffset);            
        }
        return $line;
    }
    
    public static function scanner ($input) {
        //インラインコメントを削除
        $input = self::trimSingleComment($input);
        //行Stackにpush
        self::pushSentence($input);
        //行Stackから構成
        $input = join(" ", self::getSentence());
        //行間コメントを削除
        $input = self::trimMultiComment($input);
        //重複な空白を取り除く
        $input = Parser::removeDummySpace($input);
        //有効なreplであるかどかチェックする
        $input = self::checkSentence($input);
        //有効なreplでなければ次の行を読む
        if($input === false) {
            return array(false, false, false);
        }
        //replを正規化する、例えば'a' b => 'a 'bとか, ※macro処理もここで実装予定
        $sentence = self::deform($input);
        //正規化による無駄な空白を取り除く
        $sentence = Parser::removeDummySpace($sentence);
        //パタンマッチ　※lispでは文法がないので、ここも極めてシンプルになる
        switch(true) {
        case (bool) self::isExpressionSentence($sentence):
            //S式
            $result = array($sentence, Type::Expression, $input);
            break;
        default:
            //スカラーデータ
            if (self::isNilSentence($sentence)) {
                $result = array($sentence, Type::Nil, $input);
            } else if (self::isTrueSentence($sentence)) {
                $result = array($sentence, Type::True, $input);
            } else if (self::isScalarSentence($sentence)) {
                $result = array($sentence, Type::Scalar, $input);
            } else if (self::isSymbolSentence($sentence)) {
                $result = array($sentence, Type::Symbol, $input);
            } else {
                $result = array($sentence, Parser::Group, $input);
            }
            break;
        }
        self::clearSentence();
        return $result;
    }
    //S式を正規化
    public static function deform ($sentence) {
        //処理前に正規化する
        $sentence = self::normalize ($sentence);
        return self::deformQuote($sentence);
    }
    
    public static function normalize ($sentence) {
        //: 'a' b => 'a'b
        while(strpos($sentence, "' ") !==false) {
            $sentence = str_replace("' ", "'", $sentence);
        }
        //: 'a'b => 'a 'b | 'a 'b => 'a  'b (空白は2つ)
        $sentence = str_replace("'", " '", $sentence);
        //: 'a  'b => 'a 'b (重複な空白を取り除く)
        $sentence = Parser::removeDummySpace($sentence);
        return $sentence;
    }
    
    //Macro: Quoteを展開する
    //現在はまずPHP関数で展開させるが、Macroシステムを実装できる次第、Macroで書き直す
    public static function deformQuote ($sentence) {
        if(substr_count($sentence, "'") === 0) {
            return $sentence;
        }
        if(substr($sentence, -1) === "'") {
            throw new Exception("invalid S-expression, More than one S-exp in input!");
        }
        if(substr($sentence, -2) === "')") {
            throw new Exception("invalid S-expression, More than one S-exp in input!");
        }
        //ここから、一つ一つ交換していく
        $offset = 0;
        $length = strlen($sentence);
        $stack = null;
        $beginOffset = $endOffset = null;
        $findQuote = false;
        while(($offset = strpos($sentence, "'")) !== false) {
            $findQuote = true;
            $stack = 0;
            $beginOffset = $offset;
            do {
                $char = $sentence[$offset];
                //括弧を見つかったら計上する
                if($char === "(") {
                    $stack = $stack + 1;
                }
                if($char === ")") {
                    $stack = $stack - 1;
                    if($stack < 1 && $findQuote) {
                        $endOffset = $offset;
                        //replace '... => (quote ...)
                        $sentence = substr_replace($sentence, "))", $endOffset, 1);
                        $sentence = substr_replace($sentence, "(quote ", $beginOffset, 1);
                        //update $sentence length, and set offset to 0;
                        $length = strlen($sentence);
                        $offset = 0;
                        $findQuote = false;
                    }
                }
                //あるいは、(1 '2 3)のばあい
                if($char === " " && $findQuote && $stack === 0) {
                    $endOffset = $offset;
                    //replace '... => (quote ...)
                    $sentence = substr_replace($sentence, ") ", $endOffset, 1);
                    $sentence = substr_replace($sentence, "(quote ", $beginOffset, 1);
                    //update $sentence length, and set offset to 0;
                    $length = strlen($sentence);
                    $offset = 0;
                    $findQuote = false;
                }
            } while (++ $offset < $length);
            if($findQuote) {
                //replace '... => (quote ...)
                $sentence = substr_replace($sentence, ") ", $offset, 1);
                $sentence = substr_replace($sentence, "(quote ", $beginOffset, 1);
            }
        }
        return $sentence;
    }

    //replとして成立するには、( と ) が同じ数でなればいけません　
    // ※両方とも0が可
    // ※ここでは有効なS式であるかどかのチェックを行っていません
    // ※ 成功例："1 2 3"が有効なS式ではないが，有効なreplではある
    // ※ 失敗例：")("が有効なS式ではないし、有効なreplでもないので
    public function checkSentence ($input) {
        //1、$inputが空白では次の行を読む
        if(strlen($input) === 0) {
            return false;
        }
        $firstLeft = strpos($input, "(");
        $firstRight = strpos($input, ")");
        //2、( がなくて ) があってはいけません(Error)
        if( $firstLeft === false && $firstRight !== false ) {
            throw new Exception("invalid S-expression, Invalid read syntax: ')'");
        }
        //2、) が ( よりも先にあってはいけません(Error) ※両方ともにない場合は0に変換される
        //暗黙の型変換も明示的に型変換と同じですが、明示的に型変換する方が意図を読み取れやすい
        if( (int) $firstLeft > (int) $firstRight) {
            throw new Exception("invalid S-expression, Invalid read syntax: ')'");
        }
        //3、) が ( より多くではいけません(Error)
        $countLeft = substr_count($input, "(");
        $countRight = substr_count($input, ")");
        if($countRight > $countLeft) {
            throw new Exception("invalid S-expression, Invalid read syntax: ')'");
        }
        //4、( が ) より多く場合は次の行を読む
        if($countLeft > $countRight) {
            return false;
        }
        //5、" ( ) "が存在するし、かつ ( と ) が同じ数のであれば、まずは有効なreplとして成り立つ
        return $input;
    }
    
    public static function pushSentence ($sentence_string) {
        self::$sentenceStack[] = $sentence_string;
    }

    public static function getSentence () {
        return self::$sentenceStack;
    }
    
    public static function clearSentence () {
        self::$sentenceStack = array();
    }
}
