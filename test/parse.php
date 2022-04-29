#!/usr/bin/php
<?php

use TBela\CSS\Parser;

require 'autoload.php';

$css = '
.el {
  margin: 10px calc(2vw + 5px);
  border-radius: 15px calc(15px/3) 4px 2px;
  transition: transform calc(1s - 120ms);
}

.el {
  /* Nope! */
  counter-reset: calc("My " + "counter");
}
.el::before {
  /* Nope! */
  content: calc("Candyman " * 3);
}
.el {
  width: calc(
    100%     /   3
  );
}

.el {
  width: calc(
    calc(100% / 3)
    -
    calc(1rem * 2)
  );
}
.el {
  width: calc(
   (100% / 3)
    -
   (1rem * 2)
  );
}
.el {
  width: calc(100% / 3 - 1rem * 2);
}
.el {
  /* This */
  width: calc(100% + 2rem / 2);

  /* Is very different from this */
  width: calc((100% + 2rem) / 2);
}
';

<<<<<<< HEAD
$element = (new \TBela\CSS\Parser('
p {

}

p {

margin: 1px;
'))->parse();

$element->firstChild->setChildren([]);
$element->appendCss('

p {

margin: 1px;
');

$element->deduplicate();

echo $element;
=======

$parser = (new Parser($css, ['capture_errors' => false]))->parse();

echo $parser->lastChild->lastChild;
>>>>>>> v.next
