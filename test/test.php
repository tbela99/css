#!/usr/bin/php
<?php

class Color {

    // name to color
    const COLORS_NAMES = [
        'aliceblue' => '#f0f8ff',
        'antiquewhite' => '#faebd7',
        'aqua' => '#00ffff',
        'aquamarine' => '#7fffd4',
        'azure' => '#f0ffff',
        'beige' => '#f5f5dc',
        'bisque' => '#ffe4c4',
        'black' => '#000000',
        'blanchedalmond' => '#ffebcd',
        'blue' => '#0000ff',
        'blueviolet' => '#8a2be2',
        'brown' => '#a52a2a',
        'burlywood' => '#deb887',
        'cadetblue' => '#5f9ea0',
        'chartreuse' => '#7fff00',
        'chocolate' => '#d2691e',
        'coral' => '#ff7f50',
        'cornflowerblue' => '#6495ed',
        'cornsilk' => '#fff8dc',
        'crimson' => '#dc143c',
        'cyan' => '#00ffff',
        'darkblue' => '#00008b',
        'darkcyan' => '#008b8b',
        'darkgoldenrod' => '#b8860b',
        'darkgray' => '#a9a9a9',
        'darkgrey' => '#a9a9a9',
        'darkgreen' => '#006400',
        'darkkhaki' => '#bdb76b',
        'darkmagenta' => '#8b008b',
        'darkolivegreen' => '#556b2f',
        'darkorange' => '#ff8c00',
        'darkorchid' => '#9932cc',
        'darkred' => '#8b0000',
        'darksalmon' => '#e9967a',
        'darkseagreen' => '#8fbc8f',
        'darkslateblue' => '#483d8b',
        'darkslategray' => '#2f4f4f',
        'darkslategrey' => '#2f4f4f',
        'darkturquoise' => '#00ced1',
        'darkviolet' => '#9400d3',
        'deeppink' => '#ff1493',
        'deepskyblue' => '#00bfff',
        'dimgray' => '#696969',
        'dimgrey' => '#696969',
        'dodgerblue' => '#1e90ff',
        'firebrick' => '#b22222',
        'floralwhite' => '#fffaf0',
        'forestgreen' => '#228b22',
        'fuchsia' => '#ff00ff',
        'gainsboro' => '#dcdcdc',
        'ghostwhite' => '#f8f8ff',
        'gold' => '#ffd700',
        'goldenrod' => '#daa520',
        'gray' => '#808080',
        'grey' => '#808080',
        'green' => '#008000',
        'greenyellow' => '#adff2f',
        'honeydew' => '#f0fff0',
        'hotpink' => '#ff69b4',
        'indianred' => '#cd5c5c',
        'indigo' => '#4b0082',
        'ivory' => '#fffff0',
        'khaki' => '#f0e68c',
        'lavender' => '#e6e6fa',
        'lavenderblush' => '#fff0f5',
        'lawngreen' => '#7cfc00',
        'lemonchiffon' => '#fffacd',
        'lightblue' => '#add8e6',
        'lightcoral' => '#f08080',
        'lightcyan' => '#e0ffff',
        'lightgoldenrodyellow' => '#fafad2',
        'lightgray' => '#d3d3d3',
        'lightgrey' => '#d3d3d3',
        'lightgreen' => '#90ee90',
        'lightpink' => '#ffb6c1',
        'lightsalmon' => '#ffa07a',
        'lightseagreen' => '#20b2aa',
        'lightskyblue' => '#87cefa',
        'lightslategray' => '#778899',
        'lightslategrey' => '#778899',
        'lightsteelblue' => '#b0c4de',
        'lightyellow' => '#ffffe0',
        'lime' => '#00ff00',
        'limegreen' => '#32cd32',
        'linen' => '#faf0e6',
        'magenta' => '#ff00ff',
        'maroon' => '#800000',
        'mediumaquamarine' => '#66cdaa',
        'mediumblue' => '#0000cd',
        'mediumorchid' => '#ba55d3',
        'mediumpurple' => '#9370d8',
        'mediumseagreen' => '#3cb371',
        'mediumslateblue' => '#7b68ee',
        'mediumspringgreen' => '#00fa9a',
        'mediumturquoise' => '#48d1cc',
        'mediumvioletred' => '#c71585',
        'midnightblue' => '#191970',
        'mintcream' => '#f5fffa',
        'mistyrose' => '#ffe4e1',
        'moccasin' => '#ffe4b5',
        'navajowhite' => '#ffdead',
        'navy' => '#000080',
        'oldlace' => '#fdf5e6',
        'olive' => '#808000',
        'olivedrab' => '#6b8e23',
        'orange' => '#ffa500',
        'orangered' => '#ff4500',
        'orchid' => '#da70d6',
        'palegoldenrod' => '#eee8aa',
        'palegreen' => '#98fb98',
        'paleturquoise' => '#afeeee',
        'palevioletred' => '#d87093',
        'papayawhip' => '#ffefd5',
        'peachpuff' => '#ffdab9',
        'peru' => '#cd853f',
        'pink' => '#ffc0cb',
        'plum' => '#dda0dd',
        'powderblue' => '#b0e0e6',
        'purple' => '#800080',
        'red' => '#ff0000',
        'rosybrown' => '#bc8f8f',
        'royalblue' => '#4169e1',
        'saddlebrown' => '#8b4513',
        'salmon' => '#fa8072',
        'sandybrown' => '#f4a460',
        'seagreen' => '#2e8b57',
        'seashell' => '#fff5ee',
        'sienna' => '#a0522d',
        'silver' => '#c0c0c0',
        'skyblue' => '#87ceeb',
        'slateblue' => '#6a5acd',
        'slategray' => '#708090',
        'slategrey' => '#708090',
        'snow' => '#fffafa',
        'springgreen' => '#00ff7f',
        'steelblue' => '#4682b4',
        'tan' => '#d2b48c',
        'teal' => '#008080',
        'thistle' => '#d8bfd8',
        'tomato' => '#ff6347',
        'turquoise' => '#40e0d0',
        'violet' => '#ee82ee',
        'wheat' => '#f5deb3',
        'white' => '#ffffff',
        'whitesmoke' => '#f5f5f5',
        'yellow' => '#ffff00',
        'yellowgreen' => '#9acd32',
        'rebeccapurple' => '#663399',
        'transparent' => '#00000000'
    ];

    // color to name
    const NAMES_COLORS = [
        '#f0f8ff' => 'aliceblue',
        '#faebd7' => 'antiquewhite',
        '#00ffff' => 'aqua',
        '#7fffd4' => 'aquamarine',
        '#f0ffff' => 'azure',
        '#f5f5dc' => 'beige',
        '#ffe4c4' => 'bisque',
        '#000000' => 'black',
        '#ffebcd' => 'blanchedalmond',
        '#0000ff' => 'blue',
        '#8a2be2' => 'blueviolet',
        '#a52a2a' => 'brown',
        '#deb887' => 'burlywood',
        '#5f9ea0' => 'cadetblue',
        '#7fff00' => 'chartreuse',
        '#d2691e' => 'chocolate',
        '#ff7f50' => 'coral',
        '#6495ed' => 'cornflowerblue',
        '#fff8dc' => 'cornsilk',
        '#dc143c' => 'crimson',
        '#00ffff' => 'cyan',
        '#00008b' => 'darkblue',
        '#008b8b' => 'darkcyan',
        '#b8860b' => 'darkgoldenrod',
        '#a9a9a9' => 'darkgray',
        '#a9a9a9' => 'darkgrey',
        '#006400' => 'darkgreen',
        '#bdb76b' => 'darkkhaki',
        '#8b008b' => 'darkmagenta',
        '#556b2f' => 'darkolivegreen',
        '#ff8c00' => 'darkorange',
        '#9932cc' => 'darkorchid',
        '#8b0000' => 'darkred',
        '#e9967a' => 'darksalmon',
        '#8fbc8f' => 'darkseagreen',
        '#483d8b' => 'darkslateblue',
        '#2f4f4f' => 'darkslategray',
        '#2f4f4f' => 'darkslategrey',
        '#00ced1' => 'darkturquoise',
        '#9400d3' => 'darkviolet',
        '#ff1493' => 'deeppink',
        '#00bfff' => 'deepskyblue',
        '#696969' => 'dimgray',
        '#696969' => 'dimgrey',
        '#1e90ff' => 'dodgerblue',
        '#b22222' => 'firebrick',
        '#fffaf0' => 'floralwhite',
        '#228b22' => 'forestgreen',
        '#ff00ff' => 'fuchsia',
        '#dcdcdc' => 'gainsboro',
        '#f8f8ff' => 'ghostwhite',
        '#ffd700' => 'gold',
        '#daa520' => 'goldenrod',
        '#808080' => 'gray',
        '#808080' => 'grey',
        '#008000' => 'green',
        '#adff2f' => 'greenyellow',
        '#f0fff0' => 'honeydew',
        '#ff69b4' => 'hotpink',
        '#cd5c5c' => 'indianred',
        '#4b0082' => 'indigo',
        '#fffff0' => 'ivory',
        '#f0e68c' => 'khaki',
        '#e6e6fa' => 'lavender',
        '#fff0f5' => 'lavenderblush',
        '#7cfc00' => 'lawngreen',
        '#fffacd' => 'lemonchiffon',
        '#add8e6' => 'lightblue',
        '#f08080' => 'lightcoral',
        '#e0ffff' => 'lightcyan',
        '#fafad2' => 'lightgoldenrodyellow',
        '#d3d3d3' => 'lightgray',
        '#d3d3d3' => 'lightgrey',
        '#90ee90' => 'lightgreen',
        '#ffb6c1' => 'lightpink',
        '#ffa07a' => 'lightsalmon',
        '#20b2aa' => 'lightseagreen',
        '#87cefa' => 'lightskyblue',
        '#778899' => 'lightslategray',
        '#778899' => 'lightslategrey',
        '#b0c4de' => 'lightsteelblue',
        '#ffffe0' => 'lightyellow',
        '#00ff00' => 'lime',
        '#32cd32' => 'limegreen',
        '#faf0e6' => 'linen',
        '#ff00ff' => 'magenta',
        '#800000' => 'maroon',
        '#66cdaa' => 'mediumaquamarine',
        '#0000cd' => 'mediumblue',
        '#ba55d3' => 'mediumorchid',
        '#9370d8' => 'mediumpurple',
        '#3cb371' => 'mediumseagreen',
        '#7b68ee' => 'mediumslateblue',
        '#00fa9a' => 'mediumspringgreen',
        '#48d1cc' => 'mediumturquoise',
        '#c71585' => 'mediumvioletred',
        '#191970' => 'midnightblue',
        '#f5fffa' => 'mintcream',
        '#ffe4e1' => 'mistyrose',
        '#ffe4b5' => 'moccasin',
        '#ffdead' => 'navajowhite',
        '#000080' => 'navy',
        '#fdf5e6' => 'oldlace',
        '#808000' => 'olive',
        '#6b8e23' => 'olivedrab',
        '#ffa500' => 'orange',
        '#ff4500' => 'orangered',
        '#da70d6' => 'orchid',
        '#eee8aa' => 'palegoldenrod',
        '#98fb98' => 'palegreen',
        '#afeeee' => 'paleturquoise',
        '#d87093' => 'palevioletred',
        '#ffefd5' => 'papayawhip',
        '#ffdab9' => 'peachpuff',
        '#cd853f' => 'peru',
        '#ffc0cb' => 'pink',
        '#dda0dd' => 'plum',
        '#b0e0e6' => 'powderblue',
        '#800080' => 'purple',
        '#ff0000' => 'red',
        '#bc8f8f' => 'rosybrown',
        '#4169e1' => 'royalblue',
        '#8b4513' => 'saddlebrown',
        '#fa8072' => 'salmon',
        '#f4a460' => 'sandybrown',
        '#2e8b57' => 'seagreen',
        '#fff5ee' => 'seashell',
        '#a0522d' => 'sienna',
        '#c0c0c0' => 'silver',
        '#87ceeb' => 'skyblue',
        '#6a5acd' => 'slateblue',
        '#708090' => 'slategray',
        '#708090' => 'slategrey',
        '#fffafa' => 'snow',
        '#00ff7f' => 'springgreen',
        '#4682b4' => 'steelblue',
        '#d2b48c' => 'tan',
        '#008080' => 'teal',
        '#d8bfd8' => 'thistle',
        '#ff6347' => 'tomato',
        '#40e0d0' => 'turquoise',
        '#ee82ee' => 'violet',
        '#f5deb3' => 'wheat',
        '#ffffff' => 'white',
        '#f5f5f5' => 'whitesmoke',
        '#ffff00' => 'yellow',
        '#9acd32' => 'yellowgreen',
        '#663399' => 'rebeccapurple',
        '#00000000' => 'transparent'
    ];

    public static function parseNamedColor ($str) {

        if (isset(static::COLORS_NAMES[$str])) {

            $name = static::COLORS_NAMES[$str];
            $shortened = static::shorten($name);

            if (strlen($str) > strlen($shortened)) {

                return $shortened;
            }

            if (strlen($str) > strlen($name)) {

                return $name;
            }
        }

        return $str;
    }

    public static function parseRGBColor ($str) {

        return preg_replace_callback('#rgb(a)?\s*\(\s*(\d*?\.?\d+)\s*,\s*(\d*?\.?\d+)\s*,\s*(\d*?\.?\d+)\s*(,\s*(\d*?\.?\d+))?\s*\)#s', function ($matches) {

            $color = $matches[0];

            if ($matches[1] == 'a') {
    
                if (count($matches) == 7) {

                    $color = sprintf($matches[6] == 1 ? "#%02x%02x%02x" : "#%02x%02x%02x%02x", $matches[2], $matches[3], $matches[4], 255 * $matches[6]);
                }
            }
    
            else if (count ($matches) == 5) {
    
                $color = sprintf("#%02x%02x%02x", $matches[2], $matches[3], $matches[4]);
            }

            return static::shorten($color);

        }, $str);
    }

    public static function HSLToRGB($hue, $saturation, $light) {
        // var r, g, b;
     
         if($saturation == 0){
             $r = $g = $b = $light; // achromatic
         }else{
         
             $q = $light < 0.5 ? $light * (1 + $saturation) : $light + $saturation - $light * $saturation;
             $p = 2 * $light - $q;
             $r = static::hue2rgb($p, $q, $hue + 1/3);
             $g = static::hue2rgb($p, $q, $hue);
             $b = static::hue2rgb($p, $q, $hue - 1/3);
         }
     
         return [round($r * 255), round($g * 255), round($b * 255)];
     }

    protected static function hue2rgb($p, $q, $t) {

        if($t < 0) $t += 1;
        if($t > 1) $t -= 1;
        if($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if($t < 1/2) return $q;
        if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    public static function parseHSLColor ($str) {

        return preg_replace_callback('#hsla?\s*\(\s*(\d*?\.?\d+)\s*[\s,]\s*(\d*?\.?\d+)\s*[\s,]\s*(\d*?\.?\d+)\s*\s*\)#s', function ($matches) {

            var_dump($matches[0]);

            $color = $matches[0];

            if ($matches[1] == 'a') {
    
                if (count($matches) == 7) {

                    $color = sprintf($matches[6] == 1 ? "#%02x%02x%02x" : "#%02x%02x%02x%02x", $matches[2], $matches[3], $matches[4], 256 * $matches[6]);
                }
            }
    
            else if (count ($matches) == 5) {
    
                $color = sprintf("#%02x%02x%02x", $matches[2], $matches[3], $matches[4]);
            }

            var_dump($color);
            return static::shorten($color);
    
        }, $str);
    }

    public static function shorten ($color) {

        $regExp = '\#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3';

        if (strlen($color) == 9) {

            $regExp .= '([0-9a-f])\4';
        }

        if (preg_match('#'.$regExp.'#', $color, $matches)) {

            $color = '#'.$matches[1].$matches[2].$matches[3];

            if (isset($matches[4])) {

                $color .= $matches[4];
            }
        }

        if (isset(static::NAMES_COLORS[$color]) && strlen(static::NAMES_COLORS[$color]) < 7) {

            return static::NAMES_COLORS[$color];
        }

        return $color;
    }
}

$str = '.img-polaroid {
	padding: 4px;
	background-color: #fff;
	border: 1px solid #ccc;
	border: 1px solid rgba(0, 0, 0, 0.2);
	-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
';

$str = '.img-polaroid {
42000px;
0%
0.5%;
0.20s;
200ms
1px solid #ccc;
1px solid rgba(0, 0, 0, 0.2);
0 1px 3px rgba(0, 0, 0, 0.1);
0 1px 3px rgba(0, 0, 0, 0.1);
0 1px 3px rgba(0, 0, 0, 0.1);
}
';

 echo preg_replace_callback('#([-+]?)([0-9]*\.[0-9]+|[0-9]+)(\s|([^\d\s\);,\}]*))#s', function ($matches) {

     $number = $matches[2];

     if ($number == 0) {

         return ' 0 ';
     }

     if ($matches[3] == 'ms') {

         if ($number > 100) {

             $number /= 1000;
             $matches[3] = 's';
         }
     }

         $number = explode('.', $number);

         if (isset($number[1])) {

             $number[1] = rtrim($number[1], '0');

             if ($number[0] == 0) {

                 $number[0] = '';
             }
         }

         else {

             $number[0] = preg_replace_callback('#(0{3,})$#', function ($matches) {

                 return 'e'.strlen($matches[1]);
             }, $number[0]);
         }
  //   }

    return ' '.$matches[1].implode('.', $number).$matches[3].' ';
}, $str);die;

// var_dump(Color::parseNamedColor('transparent'));
// die;
// $str = 'rgb(0,0,0) rgb(255,255,255) rgb(123,222,132) rgba(123,222,132, .5) rgba(0,0,0, .5)  rgba(0,0,0, 0)';
// var_dump(Color::parseRGBColor($str));
// function 
  
$str = '
hsl(270,60%,70%)
hsl(270, 60%, 70%)
hsl(270 60% 70%)
hsl(270deg, 60%, 70%)
hsl(4.71239rad, 60%, 70%)
hsl(.75turn, 60%, 70%)

/* These examples all specify the same color: a lavender that is 15% opaque. */
hsl(270, 60%, 50%, .15)
hsl(270, 60%, 50%, 15%)
hsl(270 60% 50% / .15)
hsl(270 60% 50% / 15%)
hsl(0,0,0) rgb(255,255,255) hsl(123,222,132) hsla(123,222,132, .5)  hsla(0,0,0, .5)';
var_dump(Color::parseHSLColor($str));

//$str = 'hsl(270, 60%, 50%, .15) hsl(270,60%,70%)';
//preg_match_all('#hsla?\s*\(\s*(\d*?\.?\d+)(%|(deg)|(rad)|(turn))?\s*?[\s,]\s*(\d*?\.?\d+)%?\s*?[\s,]\s*(\d*?\.?\d+)\s*?([\s,]\s*(\d*?\.?\d+)%?)?\s*\)#s', $str, $matches);
//var_dump($matches);

//$str = 'hsla(335, 100%, 50%, 1)';
//var_dump(Color::parseRGBColor($str));