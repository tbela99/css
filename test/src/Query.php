<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;

// because git changes \n to \r\n at some point, this causes test failure
function get_content($file) {

    return file_get_contents($file);
}


final class Query extends TestCase
{
    /**
     * @param Element $element
     * @param $jsonData
     * @param string $expected
     * @dataProvider queryProvider
     */
    public function testQuery(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

/*
*/
    public function queryProvider () {

        $data = [];

        $css = '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
h1 {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}

@media print, screen and (max-width: 12450px) {

p {
      color: #f0f0f0;
      background-color: #030303;
  }
}

@media print {
  @font-face {
    font-family: MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
  body {
    font-family: "Bitstream Vera Serif Bold", serif;
  }
  p {
    font-size: 12px;
    color: #000;
    text-align: left;
  }

  @font-face {
    font-family: Arial, MaHelvetica;
    src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
      ;
    font-weight: bold;
  }
}';

        $compiler = new Compiler();

        $compiler->setContent($css);

        $element = $compiler->getData();

        // select @font-face that contains a src declaration
        $context = '// @font-face / src / ..';

        $data[] = [
                [
                    0 => '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff")
}',
                    1 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
}',
                    2 => '@media print {
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
],
 array_map('trim', $element->query($context))];

        // select all nodes that contain a color declaration
        $context = '//* / color/ ..';

        $data[] = [
            [ 0 => 'body {
 background-color: green;
 color: #fff;
 font-family: Arial, Helvetica, sans-serif
}',
                1 => 'h1 {
 color: #fff;
 font-size: 50px;
 font-family: Arial, Helvetica, sans-serif;
 font-weight: bold
}',
                2 => '@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                3 => '@media print {
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select all @media that have the value print
        $context =  '@media[@value=print]';

        $data[] = [
            [0 =>   '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select all nodes that have the value print
        $context =  './[@value=print]';

        $data[] = [
            [0 =>   '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select all p or @media[@value=print]
        $context = '@media[@value=print],p';

        $data[] = [
            [0 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}',
                1 => '@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                2 => '@media print {
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select @media with value that begins with print
        $context = '@media[@value^=print]';

        $data[] = [
            [ 0 => '@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                1 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select @media with value that contains print
        $context = '@media[@value*=print]';

        $data[] = [
            [ 0 => '@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                1 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select @media with value that ends with print
        $context = '@media[@value$=print]';

        $data[] = [
            [ 0 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select @media with value that ends with print
        $context = '@media[@value$="print"]';

        $data[] = [
            [ 0 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select @media with value that ends with print
        $context = '@media[@value$=\'print\']';

        $data[] = [
            [ 0 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select first @media with value that begins with print
        $context = '@media[@value^=print][1]';

        $data[] = [
            [ 0 => '@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select second @media with value that begins with print
        $context = '@media[@value^=print][2]';

        $data[] = [
            [ 0 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select second @media with value that begins with print
        $context = '@media[@value^=print][2],p[1]';

        $data[] = [
            [ 0 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(MgOpenModernaBold.ttf)
 }
 body {
   font-family: "Bitstream Vera Serif Bold", serif
 }
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
 }
}',
                1 => '@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select second all nodes with property name that contains "background"
        $context = '[contains(@name, "background")]';

        $data[] = [
            [ 0 => 'body {
 background-color: green
}',
                1 => '@media print, screen and (max-width: 12450px) {
 p {
   background-color: #030303
 }
}'
            ],
            array_map('trim', $element->query($context))];

        return $data;
    }
}
