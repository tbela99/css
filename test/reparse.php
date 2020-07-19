#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

//
//$renderer = new Renderer();
//$parser = (new Compiler())->setOptions([
//        'compress' => true,])->
//        setContent('.site-grid:not(.has-sidebar-left) .container-component {
//   grid-column-start: main-start
// }
// .site-grid:not(.has-sidebar-right) .container-component {
//   grid-column-end: main-end
// }
// .site-grid > .full-width {
//   grid-column: full-start/full-end
// }')
;

//echo $parser->compile();
//$parser = (new Parser())->setOptions(['sourcemap' => true])->
//setContent('
//
//@media print {
//    thead {
//        display: table-header-group
//    }
//}
//');
//
//var_dump(
//        (string) $parser->parse(),
    //    $renderer->setOptions(['remove_comments' => true])->render($parser->parse()),
   //     json_encode($parser->parse(), JSON_PRETTY_PRINT)
//);
//die;

//$parser->appendContent('
//    pre,blockquote {
//        border: 1px solid #999;
//        page-break-inside: avoid
//    }
//');
//
//var_dump((string) $parser->parse(), $parser->parse());
//
//file_put_contents('files/test_7_parsed.css', $parser->parse());
//file_put_contents('files/test_7_sourcemap.json', json_encode($parser->parse(), JSON_PRETTY_PRINT));
//
//$parser->setOptions(['sourcemap' => false]);
//file_put_contents('files/test_7_no_sourcemap.json', json_encode($parser->parse(), JSON_PRETTY_PRINT));
//die;

for ($i = 1; $i <= 7; $i++) {

    $renderer = new Renderer();
    $parser = (new Parser())->setOptions(['sourcemap' => true])->load('files/test_'.$i.'.css');
//
//    var_dump($renderer->render($parser->parse()));
//var_dump($renderer->setOptions(['compress' => true])->render($parser->parse()));
//var_dump($parser->parse());
//var_dump($renderer->setOptions(['remove_comments' => true])->render($parser->parse()));
//var_dump(array_mapq('trim', $parser->getErrors()));

    file_put_contents('files/test_'.$i.'_parsed_comments.css', $renderer->render($parser->parse()));
    file_put_contents('files/test_'.$i.'_sourcemap.json', json_encode($parser->parse(), JSON_PRETTY_PRINT));
    file_put_contents('files/test_'.$i.'_parsed.css', $renderer->setOptions(['remove_comments' => true])->render($parser->parse()));
    file_put_contents('files/test_'.$i.'_parsed.min.css', $renderer->setOptions(['compress' => true])->render($parser->parse()));
    file_put_contents('files/test_'.$i.'_no_sourcemap.json', json_encode($parser->setOptions(['sourcemap' => false])->parse(), JSON_PRETTY_PRINT));

}


