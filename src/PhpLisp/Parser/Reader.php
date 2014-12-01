<?php

namespace PhpLisp\Parser;

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\Expression\Expression as Expression;
use PhpLisp\Expression\Type as Type;
use PhpLisp\Exception\ParseException as Exception;

class Reader {

    public static $special = array(
        "#'" => "getLambda",
        "# '" => "getLambda",
        "'," => "transform.quoteExpand",
        "' ," => "transform.quoteExpand",
        ",@" => "transform.expandList",
        ", @" => "transform.expandList",
        "'" => "quote",
        "`" => "transform.backquote",
        "," => "transform.expand");
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static $sentenceStack;
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function initialization() {
        self::$sentenceStack = array();
    }
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function isNilSentence ($sentence) {
        $sentence = str_replace(" ", "", $sentence);
        //nil
        $isNil = (strtoupper($sentence) === strtoupper(Expression::$nilInstance->nodeValue));
        //()
        $isEmpty = ($sentence === "()");
        //'()
        $isQuoteEmpty = ($sentence === "'()");
        return $isNil || $isEmpty || $isQuoteEmpty;
    }

    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
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
     * 論理上S式が有効であるかどかは実行時でないと分からない
     *  ※成功例: '(1 2 3)          論理上有効であるし、格式上有効である
     *  ※成功例: (1 2 3)　         論理上有効ではないが、格式上有効である
     *  ※失敗例: 1 2 3             論理上有効ではないし、格式上有効でもない
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

    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function isScalarSentence ($sentence) {
        //lispにおいてのスカラかどかを調べます ※つまり数値スカラか文字列スカラの判定
        $isString = self::isStringSentence($sentence);
        $isNumeric = is_numeric($sentence);
        return $isNumeric || $isString;
    }

    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
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

    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function trimSingleComment ($line) {
        $offset = strpos($line, ";");
        if($offset !== false) {
            $line = substr_replace($line, "", $offset);
        }
        return $line;
    }
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
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
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
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
        //有効なsentenceであるかどかチェックする
        $input = self::checkSentence($input);
        //有効なsentenceでなければ次の行を読む
        if($input === false) {
            return array(false, false, false);
        }
        //sentenceを正規化する、例えば'a' b => 'a 'bとか
        $sentence = self::normalize($input);
        //Debug::t($sentence, $input);
        //正規化による無駄な空白を取り除く
        $sentence = Parser::removeDummySpace($sentence);
        //パタンマッチ　※lispでは文法がないので、ここも極めてシンプルになる
        switch(true) {
            //S式
        case self::isExpressionSentence($sentence):
            $result = array($sentence, Type::Expression, $input);
            break;
            //nil構文
        case self::isNilSentence($sentence):
            $result = array($sentence, Type::Nil, $input);
            break;
            //T構文(true)
        case self::isTrueSentence($sentence):
            $result = array($sentence, Type::True, $input);
            break;
            //scalar構文
        case self::isScalarSentence($sentence):
            $result = array($sentence, Type::Scalar, $input);
            break;
            //Symbol構文
        case self::isSymbolSentence($sentence):
            $result = array($sentence, Type::Symbol, $input);
            break;
            //複数構文(関数のパラメタ)
        default:
            $result = array($sentence, Parser::Group, $input);
            break;
        }
        //一つのsentenceを構成できれば、溜めてた行を全て解放する
        self::clearSentence();
        return $result;
    }

    /**
     * S式を正規化
     * スペシャル文字を正規化する（例としては'を使う）
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function normalize ($sentence) {
        foreach(self::$special as $special => $translate) {
            //: 'a' b => 'a'b
            while(strpos($sentence, $special . " ") !==false) {
                $sentence = str_replace($special . " ", $special, $sentence);
            }
            //: 'a'b => 'a 'b 
            //'a 'b => 'a  'b (空白をあける※空白2つはできるかもしれません)
            $sentence = str_replace($special, " " . $special, $sentence);
        }
        //: 'a  'b => 'a 'b (重複な空白を取り除く)
        //$sentence = Parser::removeDummySpace($sentence);   
        $sentence = Parser::removeDummySpace($sentence);   
        foreach(self::$special as $special => $translate) {
            if(strpos($sentence, $special) === 0) {
                $test = substr_replace($sentence, "", 0, strlen($special));
                if(self::isExpressionSentence($test)
                || self::isNilSentence($test)
                || self::isTrueSentence($test)
                || self::isScalarSentence($test)
                || self::isSymbolSentence($test)) {
                    $sentence = "(" . $translate . " " . $test . ")";
                }
            }
        }
        return $sentence;
    }
    
    //replとして成立するには、( と ) が同じ数でなればいけません　
    // ※両方とも0が可
    // ※ここでは有効なS式であるかどかのチェックを行っていません
    // ※ 成功例："1 2 3"が有効なS式ではないが，有効なreplではある
    // ※ 失敗例：")("が有効なS式ではないし、有効なreplでもないので
    public static function checkSentence ($input) {
        //1、$inputが空白では次の行を読む
        if( !isset($input[0]) ) {
            return false;
        }
        $firstLeft = strpos($input, "(");
        $firstRight = strpos($input, ")");
        //2、( がなくて ) があってはいけません(Error)
        if( $firstLeft === false && $firstRight !== false ) {
            throw new Exception("invalid S-expression, Invalid read syntax: ')'");
        }
        //2、) が ( よりも先にあってはいけません(Error) 
        // 両方ともにない場合は0に変換される
        // 暗黙の型変換も明示的に型変換と同じですが、明示的に型変換する方が意図を読み取れやすい
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
        //5、" ( ) "が存在するし、かつ ( と ) が同じ数のであれば、まずは有効なsentenceとして成り立つ
        return $input;
    }
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function pushSentence ($sentence_string) {
        self::$sentenceStack[] = $sentence_string;
    }

    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function getSentence () {
        return self::$sentenceStack;
    }
    
    /**
     * 
     * @api
     * @param string $sentence
     * @return bool
     * @link
     */
    public static function clearSentence () {
        self::$sentenceStack = array();
    }
}
