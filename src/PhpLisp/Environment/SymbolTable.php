<?php
namespace PhpLisp\Environment;

use PhpLisp\Environment\Debug as Debug;
//現状では，内部的に連想配列を利用しているのですが
//将来はもし余裕や発展があれば，本物のSymbol[内部的に数値配列を利用]を実装する
class SymbolTable {
    private $Table;
    
    public function assignTable($table) {
        $this->Table = $table;
    }
    
    public function set($symbol, $node) {
        $symbol = strtoupper($symbol);
        $this->Table[$symbol] = $node;
        return $node;
    }
    
    public function get($symbol) {
        $symbol = strtoupper($symbol);
        if(isset($this->Table[$symbol])) {
            return $this->Table[$symbol];
        }
        return null;
    }

    public function has($symbol) {
        $symbol = strtoupper($symbol);
        if(isset($this->Table[$symbol])) {
            return true;
        }
        return false;
    }

    public function remove($symbol) {
        $symbol = strtoupper($symbol);
        if(isset($this->Table[$symbol])) {
            unset($this->Table[$symbol]);
        }
    }

    public function showAll() {
        Debug::p($this->Table);
    }
}