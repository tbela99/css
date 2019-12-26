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
     //  ['./css/manipulate.css']

         as $file) {

     echo $file."\n";

     $options = [];
     $parser->load($file);

     $compiler->setOptions(['compress' => false]);

     if (basename($file) == 'color.css') {

         $parser->setOptions([
             'deduplicate_declarations' => ['color']
         ]);
     }

     else {

         $parser->setOptions(['deduplicate_declarations' => true]);
     }

     $compiler->setData($parser->parse());
     $compiler->setOptions(['compress' => false, 'rgba_hex' => false]);

     file_put_contents('./output/'.basename($file), $compiler->compile());

    $compiler->setOptions(['compress' => true, 'rgba_hex' => true]);
     file_put_contents('./output/'.str_replace('.css', '.min.css', basename($file)), $compiler->compile());
 }