<?php

namespace TBela\CSS;
use TBela\CSS\Value\Number as ValueNumber;

/**
 * Class Color
 */
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

    const COLOR_HEX = '\#([a-f0-9]{8}|[a-f0-9]{6}|[a-f0-9]{4}|[a-f0-9]{3})'; // /i
    const COLOR_RGBA = 'rgb(a?)\(\s*(\d*(\.?\d+)?)(%?)\s*([,\s])\s*(\d*(\.?\d+)?)\\4\s*\\5\s*(\d*(\.?\d+)?)\\4\s*([,/]\s*(\d*(\.?\d+)?)(%?))?\s*\)'; // /s
    const COLOR_HSLA = 'hsl(a?)\(\s*(\d*(\.?\d+)?)([a-z]*)\s*([,\s])\s*(\d*(\.?\d+)?)%\s*\\5\s*(\d*(\.?\d+)?)%\s*([,/]\s*(\d*(\.?\d+)?)(%?))?\s*\)'; // /s

    public static function parseNamedColor ($str, $rgba_hex) {

        return preg_replace_callback('#\w+#', function ($matches) use ($rgba_hex) {

            $str = strtolower($matches[0]);

            if (isset(static::COLORS_NAMES[$str])) {

                $name = static::COLORS_NAMES[$str];

                $shortened = static::shorten($name);

                if (strlen($str) > strlen($shortened)) {

                    if ((strlen($shortened) == 5 || strlen($shortened) == 9) && !$rgba_hex) {

                        return static::hex2rgba($shortened);
                    }

                    return $shortened;
                }

                if (strlen($str) > strlen($name)) {

                    return $name;
                }
            }

            return static::shorten($matches[0]);
        }, $str);
    }

    public static function parseHexColor ($str, $rgba_hex) {

        return preg_replace_callback('#(?=\W?)'.static::COLOR_HEX.'(?=\W|$)#i', function ($matches) use ($rgba_hex) {

            $color = static::expand($matches[0]);
            $short = static::shorten($color);
            $length = strlen($short);

            if (isset(static::NAMES_COLORS[$color]) && $length > strlen(static::NAMES_COLORS[$color])) {

                return static::NAMES_COLORS[$color];
            }

            if (!$rgba_hex) {

                if ($length == 5 || $length == 9) {

                    return static::hex2rgba($short);
                }
            }

            return $short;

        }, $str);
    }

    public static function parseRGBColor ($str, $rgba_hex) {

        return preg_replace_callback('#(?=\W?)'.static::COLOR_RGBA.'(?=\W|$)#s', function ($matches) use ($rgba_hex) {

            $hex = $matches[0];

            /*
            if ($matches[4] == '%') {

                $matches[2] = round($matches[2] * 255 / 100);
                $matches[6] = round($matches[6] * 255 / 100);
                $matches[8] = round($matches[8] * 255 / 100);
            }

            if (isset($matches[13]) && $matches[13] == '%') {

                $matches[11] /= 100;
            }
            */

            if (!empty($matches[10])) {

                $hex = static::rgba2hex($matches[2].$matches[4], $matches[6].$matches[4], $matches[8].$matches[4], $matches[11].$matches[13]);
            }

            else {

                $hex = static::rgba2hex($matches[2].$matches[4], $matches[6].$matches[4], $matches[8].$matches[4]);
            }

            if (!$rgba_hex) {

                $length = strlen($hex);

                if ($length == 5 || $length == 9) {

                    return static::hex2rgba($hex);
                }
            }

            return $hex;

        }, $str);
    }

    public static function parseHSLColor($value, $rgba_hex) {

        return preg_replace_callback('#(?=\W?)'.static::COLOR_HSLA.'(?=\W|$)#s', function ($matches) use ($rgba_hex) {

            $hex = $matches[0];

            /*
            $matches[6] /= 100;
            $matches[8] /= 100;

            switch ($matches[4]) {

                case 'rad':

                    $matches[2] /= 2 * pi();
                    break;

                case '':
                case 'deg':

                    $matches[2] /= 360;
                    break;

                case 'turn':
                    // do nothing
                    break;
            }
            */


            if (!empty($matches[10])) {

                /*
                if ($matches[13] == '%') {

                    $matches[11] /= 100;
                }
                */

                $hex = static::hsl2hex($matches[2].$matches[4], $matches[6], $matches[8], $matches[11].$matches[13]);
            }

            else {

                $hex = static::hsl2hex($matches[2].$matches[4], $matches[6], $matches[8]);
            }

            if (!$rgba_hex) {

                $length = strlen($hex);

                if ($length == 5 || $length == 9) {

                    return static::hex2hsla($hex);
                }
            }

            return $hex;

        }, $value);
    }

    public static function hex2rgba($hex) {

        $color = static::expand($hex);

        if (isset(static::NAMES_COLORS[$color])) {

            return static::NAMES_COLORS[$color];
        }

        switch (strlen($hex)) {

            case 4;

                return 'rgb('.hexdec($hex[1].$hex[1]).','. hexdec($hex[2].$hex[2]).','. hexdec($hex[3].$hex[3]).')';

            case 5;

                return 'rgba('.hexdec($hex[1].$hex[1]).','. hexdec($hex[2].$hex[2]).','. hexdec($hex[3].$hex[3]).','. ValueNumber::compress(round(hexdec($hex[4].$hex[4]) / 255, 2)).')';

            case 7;

                return 'rgb('.hexdec($hex[1].$hex[2]).','. hexdec($hex[3].$hex[4]).','. hexdec($hex[5].$hex[6]).')';

            case 9;

                return 'rgba('.hexdec($hex[1].$hex[2]).','. hexdec($hex[3].$hex[4]).','. hexdec($hex[5].$hex[6]).','. ValueNumber::compress(round(hexdec($hex[7].$hex[8]) / 255, 2)).')';
        }

        return $hex;
    }

    public static function rgba2hsl($r, $g, $b, $a = null)
    {

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max([$r, $g, $b]);
        $min = min([$r, $g, $b]);

        $h = $s = 0;
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0; // achromatic
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }

            $h /= 6;
        }

        $h = ValueNumber::compress(round($h * 360));
        $s = ValueNumber::compress(round($s * 100));
        $l = ValueNumber::compress(round($l * 100));

        if (is_null($a) || $a === '' || $a == 1) {

            return 'hsl(' . $h . ',' . $s . '%,' . $l . '%)';
        }

        return 'hsla(' . $h . ',' . $s . '%,' . $l . '%,' . ValueNumber::compress($a) . ')';
    }

    public static function hex2hsla($hex) {

        if ($hex[0] != '#') {

            return $hex;
        }

        $hex = static::expand($hex);

        $a = '';

        if (strlen($hex) == 9) {

            $a = round(hexdec($hex[7].$hex[8]) / 255, 2);
        }

        return static::rgba2hsl(hexdec($hex[1].$hex[2]), hexdec($hex[3].$hex[4]), hexdec($hex[5].$hex[6]), $a);
    }

    public static function rgba2hex($r, $g, $b, $a = null) {

        $r = (string) $r;
        $g = (string) $g;
        $b = (string) $b;

        if (substr($r, -1) == '%') {

            $r = floatval($r) * 255 / 100;
        }

        if (substr($g, -1) == '%') {

            $g = floatval($g) * 255 / 100;
        }

        if (substr($b, -1) == '%') {

            $b = floatval($b) * 255 / 100;
        }

        if (!is_null($a) && $a !== '') {

            if ($a[strlen($a) - 1] == '%') {

                $a = floatval($a) / 100;
            }
        }

        $color = sprintf(is_null($a) || $a == 1 || $a === '' ? "#%02x%02x%02x" : "#%02x%02x%02x%02x", $r, $g, $b, 255 * $a);

        $short = static::shorten($color);

        if (isset(static::NAMES_COLORS[$color]) && strlen($short) > strlen(static::NAMES_COLORS[$color])) {

            return static::NAMES_COLORS[$color];
        }

        return $short;
    }

    public static function hsl2hex($h, $s, $l, $a = null) {

        switch (preg_replace('#^(\d*(\.?\d+)?)+#', '', $h)) {

            case 'rad':

                $h = floatval($h) / (2 * pi());
                break;

            case '':
            case 'deg':

                $h = floatval($h) / 360;
                break;

            case 'turn':
                // do nothing
                $h = floatval($h);
                break;
        }

        $s = floatval($s) / 100;
        $l = floatval($l) / 100;

        if (!is_null($a) && $a !== '') {

            if (substr($a, -1) == '%') {

                $a = floatval($a) / 100;
            }
        }

        $r = $l;
        $g = $l;
        $b = $l;
        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);

        if ($v > 0){

            $m = $l + $l - $v;
            $sv = ($v - $m ) / $v;
            $h *= 6.0;
            $sextant = floor($h);
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;

            switch ($sextant)
            {
                case 0:
                    $r = $v;
                    $g = $mid1;
                    $b = $m;
                    break;
                case 1:
                    $r = $mid2;
                    $g = $v;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $v;
                    $b = $mid1;
                    break;
                case 3:
                    $r = $m;
                    $g = $mid2;
                    $b = $v;
                    break;
                case 4:
                    $r = $mid1;
                    $g = $m;
                    $b = $v;
                    break;
                case 5:
                    $r = $v;
                    $g = $m;
                    $b = $mid2;
                    break;
            }
        }

        $hex = sprintf(is_null($a) || $a == 1 ? "#%02x%02x%02x" : "#%02x%02x%02x%02x", round($r * 255), round($g * 255), round($b * 255), round(255 * $a));

        $short = static::shorten($hex);

        if (isset(static::NAMES_COLORS[$hex]) && strlen($short) > strlen(static::NAMES_COLORS[$hex])) {

            return static::NAMES_COLORS[$hex];
        }

        return $short;
    }

    public static function expand ($color) {

        $color = strtolower($color);

        if ($color[0] != '#') {

            return $color;
        }

        if (strlen($color) > 7) {

            if ($color[7].$color[8] == 'ff') {

                return substr($color, 0, 7);
            }

            return $color;
        }

        $expanded = '#'.$color[1].$color[1]. $color[2].$color[2]. $color[3].$color[3];

        if (strlen ($color) == 5) {

            if ($color[4] != 'f') {

                $expanded .= $color[4].$color[4];
            }
        }

        return $expanded;
    }

    public static function shorten ($str) {

        $regExp = '\#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3';

        $color = strtolower($str);

        if (strlen($color) == 9) {

            $regExp .= '([0-9a-f])\4';
        }

        if (preg_match('#'.$regExp.'#', $color, $matches)) {

            $color = '#'.$matches[1].$matches[2].$matches[3];

            if (isset($matches[4]) && $matches[4] != 'f') {

                $color .= $matches[4];
            }

            if (isset(static::NAMES_COLORS[$color]) && strlen(static::NAMES_COLORS[$color]) < 7) {

                return static::NAMES_COLORS[$color];
            }

            return $color;
        }

        return $str;
    }
}
