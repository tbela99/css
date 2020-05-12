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

     $parser = new Parser('', ['flatten_import' => true]);
     $compiler = new Compiler();

     $compiler->setOptions(['convert_color' => true, 'css_level' => 4]);

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

     $compiler->setData($parser->load($file)->parse());
     $compiler->setOptions(['compress' => false, 'rgba_hex' => false]);

     file_put_contents('./output/'.basename($file), $compiler->compile());

    $compiler->setOptions(['compress' => true, 'convert_color' => true, 'css_level' => 4]);
     file_put_contents('./output/'.str_replace('.css', '.min.css', basename($file)), $compiler->compile());
 }