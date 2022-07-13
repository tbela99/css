#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

require 'autoload.php';

//$proertyList = new \TBela\CSS\Property\PropertyList();
//
//$proertyList->set('margin', '2px !important');
//$proertyList->set('margin-left', '3px !important');
//
//
//echo $proertyList;

$parser = new Parser('.widget-rss.red .title,
.widget-recent .title {
  color: red;
}
aside .widget-rss:hover {
  background: #fff;
}');

$stylesheet = $parser->parse();


foreach ($stylesheet->query("[value*='.widget-rss']") as $p) {
    foreach($p->getSelector() as $selector) {
        if(strpos($selector, '.widget-rss') !== false) {

            try {

                $p->removeSelector($selector);
            }

            catch (Exception $e) {

                // empty selector
                $p['parentNode']->remove($p);
            }
        }
    }
}

//try {

    echo $stylesheet;
//}
//
//catch (Exception $e) {
//
//    echo $e->getMessage();
//}
//var_dump($parser->getAst());

//echo $parser;