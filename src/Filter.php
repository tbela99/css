<?php

namespace TBela\CSS;

/**
 * Class Filter
 */
class Filter {

    /**
     * @var Renderer
     */
    protected $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * parse value
     * @param string $value
     * @param Element $element
     * @return string
     */
    public function value ($value, Rendererable $element) {

        $type = (string) $element['type'];

        if ($type == 'atrule') {

            if ($value !== '') {

                // rewrite atrule @somename url(https://foobar) -> @somename https://foobar
                $value = preg_replace('#url\(\s*(["\']?)(.*?)\1\)#s', '$2', $value);
            }
        }

        else {

            // remove quotes
            $value = preg_replace('#url\(\s*(["\']?)(.*?)\1\)#s', 'url($2)', $value);

            if (in_array($type, ['declaration', 'property']) && $this->renderer->getOptions('compress')) {

                $name = (string) $element['name'];

                switch ($name) {

                    case 'border':
                    case 'border-top':
                    case 'border-right':
                    case 'border-left':
                    case 'border-bottom':
                    case 'outline':

                        if (trim ($value) == 'none') {

                            $value = '0';
                        }

                        break;

                    case 'font':
                    case 'font-weight':

                        $value = preg_replace_callback('#(^|\b)((bold)|(normal))(\b)#', function ($matches) {

                            return $matches[1].($matches[2] == 'bold' ? 700 : 400);
                        }, $value);

                        break;
                }
            }
        }

        return $value;
    }

    /**
     * remove unnecessary whitespace
     * @param string $value
     * @return string
     */
    public function whitespace($value) {

        $value = preg_replace('#\s*([\[\]\(\),])\s*#s', '$1', $value);
        $value = preg_replace('#\s*([\),])(?![;,])#s', '$1 ', $value);

        // separate values with a single space character?
        return preg_replace('#\s+#sm', ' ', $value);
    }

    /**
     * convert colors
     * @param string $value
     * @param Element $element
     * @return string
     */
    public function color($value, Element $element) {

        if ($element->getName(false) == 'filter' && strpos($value, 'progid:DXImageTransform.Microsoft.') !== false) {

            return $value;
        }

        $rgba_hex = $this->renderer->getOptions('rgba_hex');

        if (preg_match('#\#[a-f0-9]{3,}#i', $value)) {

            $value = Color::parseHexColor($value, $rgba_hex);
        }

        if (strpos($value, 'rgb') !== false) {

            $value = Color::parseRGBColor($value, $rgba_hex);
        }

        if (strpos($value, 'hsl') !== false) {

            $value = Color::parseHSLColor($value, $rgba_hex);
        }

        return Color::parseNamedColor($value, $rgba_hex);
    }

    /**
     * convert numbers 1.0 -> 1, 1.20 -> 1.2, 2000 -> 2e3, 200ms -> .2s, ...
     * @param string $value
     * @return string
     */
    public function numbers($value) {

        if (!$this->renderer->getOptions('compress')) {

            return $value;
        }

        return preg_replace_callback('#(^|[-+\(\s])([0-9]*\.?[0-9]+)([^\d\s\);,\}]*|\s)#s', function ($matches) {

            if ($matches[3] != '' && !preg_match('#^[a-zA-Z]+$#', $matches[3])) {

                return $matches[0];
            }

            $character = $matches[1];

            array_splice($matches, 1, 1);

            $matches = array_values($matches);

            $number = $matches[1];

            // remove unit
            if ($number == 0) {

                if ($character == '-' || $character == '+') {

                    $character = '';
                }

                return $character.'0 ';
            }

            // convert 'ms' to 's'
            if ($matches[2] == 'ms') {

                if ($number >= 100) {

                    $number /= 1000;
                    $matches[2] = 's';
                }
            }

            $number = explode('.', $number);

            if (isset($number[1]) && $number[1] == 0) {

                unset($number[1]);
            }

            if (isset($number[1])) {

                // convert 0.20 to .2
                $number[1] = rtrim($number[1], '0');

                if ($number[0] == 0) {

                    $number[0] = '';
                }

            } else {

                // convert 1000 to 1e3
                $number[0] = preg_replace_callback('#(0{3,})$#', function ($matches) {

                    return 'e' . strlen($matches[1]);
                }, $number[0]);
            }

            return $character. implode('.', $number) . $matches[2] . ' ';
        }, $value);
    }
}
