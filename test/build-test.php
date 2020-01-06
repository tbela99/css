#!/usr/bin/php
<?php

require 'autoload.php';
use \TBela\CSS\Parser;
use \TBela\CSS\Compiler;

// test builder

 foreach (
     glob('css/*.css')
     //  ['./css/manipulate.css']

         as $file) {

     $parser = new Parser($file, ['flatten_import' => true]);
     $compiler = new Compiler();

     $compiler->setOptions(['rgba_hex' => true]);

     echo $file."\n";

     $compiler->setOptions(['compress' => false]);

     if (basename($file) == 'color.css') {

         $parser->setOptions([
             'allow_duplicate_declarations' => ['color']
         ]);
     }

     else {

         $parser->setOptions(['allow_duplicate_declarations' => true]);
     }

     $compiler->setData($parser->parse());
     $compiler->setOptions(['compress' => false, 'rgba_hex' => false]);

     file_put_contents('./output/'.basename($file), $compiler->compile());

    $compiler->setOptions(['compress' => true, 'rgba_hex' => true]);
     file_put_contents('./output/'.str_replace('.css', '.min.css', basename($file)), $compiler->compile());
 }