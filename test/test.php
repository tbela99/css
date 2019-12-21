#!/usr/bin/php
<?php

require 'autoload.php';
// test builder

$parser = new \TBela\CSS\Parser();
$compiler = new \TBela\CSS\Compiler();

$parser->setOptions(['flatten_import' => true]);
$compiler->setOptions(['rgba_hex' => true]);

 foreach (
      glob('css/*.css')

         as $file) {

     echo $file."\n";

     $options = [];
     $parser->load($file);

     $compiler->setOptions(['compress' => false]);
     $compiler->setData($parser->parse());
    file_put_contents('./output/'.basename($file), $compiler->compile());

    $compiler->setOptions(['compress' => true]);
     file_put_contents('./output/'.str_replace('.css', '.min.css', basename($file)), $compiler->compile());
 }

/*
foreach (
    //  glob('./css/*.css')
    [
            './css/import.css',
     //       './css/import-media.css'
    ]
    as $file) {

    echo $file."\n";

    $parser->setOptions(['flatten_import' => true]);
    $parser->load($file);

    $compiler->setData($parser->parse());
    $compiler->setOptions(['compress' => false]);
    file_put_contents('./output/'.str_replace('.css', '.import.css', basename($file)), $compiler->compile());

    $compiler->setOptions(['compress' => true]);
   // var_dump($compiler);
    file_put_contents('./output/'.str_replace('.css', '.import.min.css', basename($file)), $compiler->compile());
}
*/