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
     * @param array $expected
     * @param array $actual
     * @dataProvider queryProvider
     */
    public function testQuery(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider querySelectorProvider
     */
    public function testQuerySelector(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider combinatorProvider
     */
    public function testCombinator(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider queryFunctionsProvider
     */
    public function testQueryFunctions(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider queryProviderOr
     */
    public function testQueryOr(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider renderQuery
     */
    public function testRenderQuery(string $expected, string $actual): void
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
  src: url(/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff)
}',
                    1 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
 }
}',
                    2 => '@media print {
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
                2 => '@media print, screen and(max-width:12450px) {
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
 }
}',
                1 => '@media print, screen and(max-width:12450px) {
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
            [ 0 => '@media print, screen and(max-width:12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                1 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select @media with value that contains print
        $context = '@media[@value*=print]';

        $data[] = [
            [ 0 => '@media print, screen and(max-width:12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                1 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
 }
}'
            ],
            array_map('trim', $element->query($context))];

        // select first @media with value that begins with print
        $context = '@media[@value^=print][1]';

        $data[] = [
            [ 0 => '@media print, screen and(max-width:12450px) {
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
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
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
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
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
 }
}',
                1 => '@media print, screen and(max-width:12450px) {
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
                1 => '@media print, screen and(max-width:12450px) {
 p {
   background-color: #030303
 }
}'
            ],
            array_map('trim', $element->query($context))];

        return $data;
    }

    public function querySelectorProvider () {

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
        $context = '// @font-face / src / .. | @media[@value^=print][1],p';

        $data[] = [
            [
                0 => '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url(/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff)
}',
                1 => '@media print, screen and(max-width:12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}',
                2 => '@media print {
 @font-face {
   font-family: MaHelvetica;
   font-weight: bold;
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
 }
}',
                3 => '@media print {
 p {
   font-size: 12px;
   color: #000;
   text-align: left
 }
}',
                4 => '@media print {
 @font-face {
   font-family: Arial, MaHelvetica;
   font-weight: bold;
   src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local(HelveticaNeue-Bold)
 }
}'
            ],
            array_map('trim', $element->query($context))];

        return $data;
    }

    public function queryFunctionsProvider () {

        $data = [];

        $css = '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}
p:before {
content: "print";
color: rgb(255 0 0 / 1);
}
@media print {

}
/** this is the story */
/** of the princess leia */
/** who was luke sister */
body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
strong {

}
p {

}
a {

color: white;
}
span {
color: #343434;
}

h1,h2, a {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}';

        $compiler = new Compiler();

        $compiler->setContent($css);

        $element = $compiler->getData();

        // select @font-face that contains a src declaration
        $context = '[color(@value, "red")]';

        $data[] = [
            [
                0 => 'p:before {
 color: red
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[equals(@name, "src")]/..';

        $data[] = [
            [
                0 => '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url(/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff)
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[equals(@value, print)]';

        $data[] = [
            [
                0 => '@media print {

}',
  1 => 'p:before {
 content: print
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[comment()]';

        $data[] = [
            [
                0 => '/** this is the story */',
                1 => '/** of the princess leia */',
                2 => '/** who was luke sister */'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[empty()]';

        $data[] = [
            [
                0 => '@media print {

}',
                1 => 'strong {

}',
                2 => 'p {

}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[contains(@name, "background")]';

        $data[] = [
            [
                0 => 'body {
 background-color: green
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[equals(@name, "color")][not(color(@value, "white"))]';

        $data[] = [
            [
                0 => 'p:before {
 color: red
}',
                1 => 'span {
 color: #343434
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[beginswith(@name, "color")][not(color(@value, "white"))]';

        $data[] = [
            [
                0 => 'p:before {
 color: red
}',
                1 => 'span {
 color: #343434
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[endswith(@name, "color")][not(color(@value, "white"))]';

        $data[] = [
            [
                0 => 'p:before {
 color: red
}',
                1 => 'body {
 background-color: green
}',
                2 => 'span {
 color: #343434
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '// @font-face / src / .. | body | p:before';

        $data[] = [
            [
                0 => '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url(/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff)
}',
                1 => 'p:before {
 content: print;
 color: red
}',
                2 => 'body {
 background-color: green;
 color: #fff;
 font-family: Arial, Helvetica, sans-serif
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[equals(@name, "color")][not(color(@value, "white"))]';

        $data[] = [
            [
                0 => 'p:before {
 color: red
}',
                1 => 'span {
 color: #343434
}'
            ],
            array_map('trim', $element->query($context))];

        //
        $context = '[beginswith(@name, "color")][not(color(@value, "white"))]';

        $data[] = [
            [
                0 => 'p:before {
 color: red
}',
                1 => 'span {
 color: #343434
}'
            ],
            array_map('trim', $element->query($context))];

        return $data;
    }
    /*
    */
    public function queryProviderOR ()
    {

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
a {

color: white;
}
span {
color: #343434;
}

h1,h2, a {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}';

        $compiler = new Compiler();

        $compiler->setContent($css);

        $element = $compiler->getData();

        // select @font-face that contains a src declaration
        $context = 'h1,a';

        $data[] = [
            [
                0 => 'a {
 color: #fff
}',
                1 => 'h1,
h2,
a {
 color: #fff;
 font-size: 50px;
 font-family: Arial, Helvetica, sans-serif;
 font-weight: bold
}'
            ],
            array_map('trim', $element->query($context))];

        return $data;
    }
    /*
    */
    public function renderQuery ()
    {

        $data = [];

        $parser = new \TBela\CSS\Query\Parser();

        $query = '.select-menu-item .octicon-check, .select-menu-item .octicon-circle-slash, .select-menu-item input[type="radio"]:not(:checked) + .octicon-check, .select-menu-item input[type="radio"]:not(:checked) + .octicon-circle-slash';

        $data[] = [
            '.select-menu-item .octicon-check, .select-menu-item .octicon-circle-slash, .select-menu-item input[type=radio]:not(:checked) + .octicon-check, .select-menu-item input[type=radio]:not(:checked) + .octicon-circle-slash',
            (string) $parser->parse($query)
        ];

        $query = ' . / [ @value = "print" ] ';
        $data[] = [
            './[@value=print]',
            (string) $parser->parse($query)
        ];

        $query = ' [ contains( @name , "background" ) ]';
        $data[] = [
            '[@name*=background]',
            (string) $parser->parse($query)
        ];

        $query = ' [ not( color( @value , "white") ) ] ';
        $data[] = [
            '[not(color(value,#fff))]',
            (string) $parser->parse($query)
        ];

        $query = ' [ equals( @name , "color" ) ] ';
        $data[] = [
            '[@name=color]',
            (string) $parser->parse($query)
        ];

        $query = ' [ beginswith( @name , "color" ) ] ';
        $data[] = [
            '[@name^=color]',
            (string) $parser->parse($query)
        ];

        $query = '.select-menu-item
        .octicon-check';
        $data[] = [
            '.select-menu-item .octicon-check',
            (string) $parser->parse($query)
        ];

        $query = '.select-menu-item
        .octicon-check | [ beginswith( @name , "color" ) ] ';
        $data[] = [
            '.select-menu-item .octicon-check|[@name^=color]',
            (string) $parser->parse($query)
        ];

        $query = '// @font-face / src / ..';
        $data[] = [
            '//@font-face/src/..',
            (string) $parser->parse($query)
        ];

        $query = '//* / color/ ..';
        $data[] = [
            '//*/color/..',
            (string) $parser->parse($query)
        ];

        $query = '[@name]';
        $data[] = [
            '[@name]',
            (string) $parser->parse($query)
        ];

        return $data;
    }
    /*
    */
    public function combinatorProvider ()
    {

        $data = [];


        $css = 'input[ name $= "foo_bar" ], strong {

    background: blue;
}';

        $query = '[name $= foo_bar]';

        $compiler = new Compiler();
        $compiler->setContent($css);

        $data[] = [
            [
                0 => 'input[name$=foo_bar],
strong {
 background: blue
}'],
            array_map('trim', $compiler->getData()->query($query))
        ];


        return $data;
    }
}

