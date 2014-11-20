#!/usr/bin/env php
<?php
require "../vendor/autoload.php";

use PhpLisp\PhpLisp as PhpLisp;
use PhpLisp\Environment\Debug as Debug;

Debug::$mode = true;
PhpLisp::initialization();
PhpLisp::write("PhpLisp (Lisp is implement by PHP) 0.1.0 Nov 14 2014 18:11");
PhpLisp::write(PHP_EOL);
PhpLisp::write("Source License: MIT");
PhpLisp::write(PHP_EOL);
PhpLisp::write("Author: Chen Han/陈瀚");
PhpLisp::write(PHP_EOL);
PhpLisp::write("Email: gpgkd906@gmail.com");
PhpLisp::write(PHP_EOL);
PhpLisp::write("Github: https://github.com/gpgkd906/PhpLisp");
PhpLisp::write(PHP_EOL);
PhpLisp::write("Use (help) to get some basic information about PhpLisp.");
PhpLisp::write(PHP_EOL);
PhpLisp::write("Use exit to terminal the repl.");
PhpLisp::write(PHP_EOL);
PhpLisp::write(PHP_EOL);
PhpLisp::repl();