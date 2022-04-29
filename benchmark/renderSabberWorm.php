#!/usr/bin/php
<?php

ob_start();
require_once 'parseSabberWorm.php';
ob_clean();
/**
 * @var string $stylesheet
 */

echo $stylesheet->render(($argv[1] ?? null) == '-c' ? Sabberworm\CSS\OutputFormat::createCompact() : Sabberworm\CSS\OutputFormat::createPretty());
