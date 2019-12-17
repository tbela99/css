#!/usr/bin/php
<?php

require 'autoload.php';

$parser = new \TBela\CSS\Parser(file_get_contents('./css/multiple2.css'), [
    'silent' => false,
    'flatten_import' => true
]);
//$parser;
file_put_contents('out.json', json_encode($parser->parse()));