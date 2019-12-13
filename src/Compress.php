<?php 

namespace TBela\CSS;

/**
 * remove white space and empty declarations for now
 * @todo optimize css properties
 * - remove unit from 0
 * - optimize color
 * - compute short-hand properties? (color, background, border, border-radius, etc)?
 */

const MATCH_WORD = '/"(?:\\"|[^"])*"|\'(?:\\\'|[^\'])*\'/s';

/**
 * Print minified CSS
 * @package CSS
 */
class Compress extends Identity {

    public function __construct(array $options) {

        parent::__construct($options);

        $this->glue = '';
        $this->indent = '';
        $this->remove_comments = true;
        $this->remove_empty_nodes = true;
    }

    protected function renderValue ($value, $type = null) {

        $replace = [];

        if ($type == 'atrule') {

            if ($value !== '') {
               
                // rewrite atrule @somename url(https://foobar) -> @somename https://foobar
                $value = preg_replace('#url\(\s*(["\']?)(.*?)\1\)#s', '$2', $value);    
            }         
        }

        else {
        
            // remove quotes
            $value = preg_replace('#url\(\s*(["\']?)(.*?)\1\)#s', 'url($2)', $value);    
        }

        if ($type == 'declaration') {

            if (preg_match('#\#[a-f0-9]{3,}#i', $value)) {

                $value = Color::parseHexColor($value, $this->rgba_hex);
            }

            if (preg_match('#\brgba?#', $value)) {

                $value = Color::parseRGBColor($value, $this->rgba_hex);
            }

            if (preg_match('#\bhsla?#', $value)) {

                $value = Color::parseHSLColor($value, $this->rgba_hex);
            }

        //    else {

                $value = Color::parseNamedColor($value, $this->rgba_hex);
        //    }
        }

        
        // hash quoted words
        $value = preg_replace_callback(MATCH_WORD, function ($matches) use(&$replace) {

            if (empty($matches[1])) {

                return $matches[0];
            }

            $replace[$matches[1]] = '~~'.crc32($matches[1]).'~~';

            return str_replace($matches[1], $replace[$matches[1]], $matches[0]);

        }, $value);

        // parse numbers
        $value = preg_replace_callback('#([-+]?)([0-9]*\.[0-9]+|[0-9]+)(\s|([^\d\s\);,\}]*))#s', function ($matches) {

            $number = $matches[2];

            // remove unit
            if ($number == 0) {

                return ' 0 ';
            }

            // convert 'ms' to 's'
            if ($matches[3] == 'ms') {

                if ($number > 100) {

                    $number /= 1000;
                    $matches[3] = 's';
                }
            }

            $number = explode('.', $number);

            if (isset($number[1])) {

                // convert 0.20 to .2
                $number[1] = rtrim($number[1], '0');

                if ($number[0] == 0) {

                    $number[0] = '';
                }
            }

            else {

                // convert 1000 to 1e3
                $number[0] = preg_replace_callback('#(0{3,})$#', function ($matches) {

                    return 'e'.strlen($matches[1]);
                }, $number[0]);
            }

            return ' '.$matches[1].implode('.', $number).$matches[3].' ';
        }, $value);

        // remove unnecessary space
        $value = preg_replace('#\s*([\[\]\(\),])\s*#s', '$1', $value);

        // remove extra space
        $value = preg_replace('#\s+#s', ' ', $value);
        $value = str_replace(array_values($replace), array_keys($replace), $value);

        return trim($value);
    }
    
    /**
     * @param ElementDeclaration $element
     * @return string
     */
	protected function renderDeclaration (ElementDeclaration $element) {

	    $vendor = $element->getVendor();
	    $name = $element->getName(false);

        $value = $this->renderValue($element->getValue(), $element->getType());

        switch ($name) {

            case 'margin':
            case 'padding':
            case 'border-width':
            case 'border-radius':

                $value = explode(' ', $value);

                if (isset ($value[1])) {

                    if (isset ($value[3])) {

                        if ($value[3] == $value[1]) {

                            unset ($value[3]);
                        }
                    }

                    if (count ($value) == 3) {

                        if ($value[0] == $value[2]) {

                            unset ($value[2]);
                        }
                    }

                    if (count ($value) == 2) {

                        if ($value[0] == $value[1]) {

                            unset ($value[1]);
                        }
                    }
                }

            $value = implode(' ', $value);
            break;
        }

        if ($vendor !== '') {

            $vendor = '-'.$vendor.'-';
        }

	    return $this->renderName($vendor.$name).':'.$this->indent.$value;
    }

}

