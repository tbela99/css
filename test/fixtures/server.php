<?php

use TBela\CSS\Parser;

chdir(__DIR__);

define('STDERR', fopen('php://stderr', 'w'));

require __DIR__.'/../autoload.php';

function ellipsis ($string, $length = 15) {

    if (mb_strlen($string) < $length - 2) {

        return $string;
    }

    return mb_substr($string, 0, $length - 3).'...';
}

function toFileSize(float $size, array $units = [])
{

	if ($size == 0) return 0;

	$s = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
	$e = floor(log($size) / log(1024));

	return sprintf("%.2f%s", $size / pow(1024, floor($e)), $units[$e] ?? $s[$e]);
}

function toDuration(float $duration)
{

	return sprintf("%.2f%s", $duration < 1 ? $duration * 1000 : $duration, $duration < 1 ? 'ms' : 's');
}

$parser = new Parser();
fwrite(STDERR, sprintf("\n\n\ncurl dir %s >>>\n", getcwd()));

echo $parser->setOptions([
	'multi_processing' => true,
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
on('pool.start', function (int $index, $thread) use (&$parsingStartTime) {

	fwrite(STDERR, sprintf("starting %s #%d - elapsed %s\n", preg_replace('#.*\\\\(.+)$#', '$1', $thread::class), $index, toDuration(microtime(true) - $parsingStartTime)));
	fwrite(STDERR, sprintf("memory usage: %s peak: %s\n", toFileSize(memory_get_usage(true)), toFileSize(memory_get_peak_usage(true))));
})->
on('pool.finish', function (array|string $result, int $index, ?string $stderr, ?int $exitCode, ?string $duration, $process) use (&$parsingStartTime) {

	fwrite(STDERR, sprintf("%s %d finished in %s and exited with status %s - elapsed %s\n", preg_replace('#.*\\\\(.+)$#', '$1', $process::class), $index, $duration, $exitCode, toDuration(microtime(true) - $parsingStartTime)));
	fwrite(STDERR, sprintf("memory usage: %s peak: %s\n", toFileSize(memory_get_usage(true)), toFileSize(memory_get_peak_usage(true))));
})->
load($_SERVER['REQUEST_URI']);