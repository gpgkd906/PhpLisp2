#!/usr/bin/env php
<?php
require "../vendor/autoload.php";

use PhpLisp\Environment\Debug as Debug;
use PhpLisp\PhpLisp as PhpLisp;

Debug::$mode = true;
PhpLisp::initialization();
PhpLisp::interpreter("test.lisp");