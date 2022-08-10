<?php

use TBela\CSS\Parser;

chdir(__DIR__);

define('STDERR', fopen('php://stderr', 'w'));

require 'autoload.php';

function ellipsis ($string, $length = 15) {

    if (mb_strlen($string) < $length - 2) {

        return $string;
    }

    return mb_substr($string, 0, $length - 3).'...';
}

$parser = new Parser();
fwrite(STDERR, sprintf("curl dir %s >>>\n", getcwd()));

echo $parser->setOptions([
	'multi_processing' => false,
    'flatten_import' => true,
    'capture_errors' => false
])->
on('start', function ($token) {

    fwrite(STDERR, sprintf(">>> start parsing document %s\n", $token->src ?? ''));
})->
on('end', function ($token) {

    fwrite(STDERR, sprintf("<<< end parsing document %s\n\n", $token->src ?? ''));
})->
on('error', function ($exception) {

    fwrite(STDERR, sprintf("error: => %s\n", $exception));
})->
on('enter', function ($token) {

    $value = !isset($token->value) ? '' :
        (is_array($token->value) ? \TBela\CSS\Value::renderTokens($token->value) : $token->value);

    if ($token->type == 'AtRule' && $token->name == 'import') {

        fwrite(STDERR, sprintf("-> enter %s(%s#%s) at %s:%s:%s\n", $token->type,
            $token->name,
            $value,
            $token->src ?? '',
            $token->location->start->line,
            $token->location->start->column
        ));
    }

    else {

        fwrite(STDERR, sprintf("-> enter %s(%s) at %s:%s:%s\n", $token->type,
            $token->name ?? ($token->selector ?? ellipsis($value, 40)),
            $token->src ?? '',
            $token->location->start->line,
            $token->location->start->column
        ));
    }
})->
on('exit', function ($token) {

    $value = !isset($token->value) ? '' :
        (is_array($token->value) ? \TBela\CSS\Value::renderTokens($token->value) : $token->value);

    if ($token->type == 'AtRule' && $token->name == 'import') {

        fwrite(STDERR, sprintf("-> enter %s(%s#%s) at %s:%s:%s\n", $token->type,
            $token->name,
            $value,
            $token->src ?? '',
            $token->location->end->line,
            $token->location->end->column
        ));
    }

    else {

        fwrite(STDERR, sprintf("-> exit %s(%s) at %s:%s:%s\n", $token->type,
            $token->name ?? ($token->selector ?? ellipsis($value, 40)),
            $token->src ?? '',
            $token->location->end->line,
            $token->location->end->column
        ));
    }
})->
load($_SERVER['REQUEST_URI']);