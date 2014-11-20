#!/usr/bin/env php
<?php
require dirname(__FILE__) . "/../vendor/autoload.php";

error_reporting (E_ALL | E_STRICT);

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\PhpLisp as PhpLisp;

Debug::$mode = true;
PhpLisp::initialization();
PhpLisp::interpreter("test.lisp");