#!/usr/bin/env php
<?php
require "../vendor/autoload.php";

use PhpLisp\PhpLisp as PhpLisp;

PhpLisp::initialization();
PhpLisp::script("test.lisp");