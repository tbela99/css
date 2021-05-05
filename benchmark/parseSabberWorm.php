#!/usr/bin/php
<?php
require 'vendor/autoload.php';
require_once 'css.php';

$parser = new Sabberworm\CSS\Parser($css);

$stylesheet = $parser->parse();

//echo strlen($doc->render(Sabberworm\CSS\OutputFormat::createCompact()));