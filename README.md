PhpLisp is yet another Lisp Interpreter/Dialect written in PHP.

    OLD PhpLisp: https://github.com/gpgkd906/PhpLisp
    
    Inherited from old PhpLisp:
    Environment\Environment
    Environment\SymbolTable
    Environment\Debug
    Exception\Exception
    Exception\EvalException
    Exception\ParseException
    PhpLisp
    
    completed task
    rewrite Expression\Expression
    rewrite Expression\Type
    rewrite Parser\Reader
    rewrite Parser\Parser
    add Environment\Stack
    add composer support
    Evaluator\Evaluator rewrite
    replace Evaluator\Lambda with Evaluator\LambdaEvaluator
    Environment\Processor rewrite
    
    uncompleted task
    add Parser\Macro