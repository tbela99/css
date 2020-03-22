<?php

namespace TBela\CSS\Value;

use TBela\CSS\Color as ColorUtil;
use TBela\CSS\Value;

/**
 * Css Color Value
 * Class Color
 * @package TBela\CSS\Value
 */
class Color extends Value
{

    /**
     * Test whether data matches this class
     * @param $data
     * @return bool
     */
    protected static function validate($data)
    {

        if (isset($data->name) && isset($data->arguments)) {

            return in_array($data->name, ['rgb', 'rgba', 'hsl', 'hsla']);
        }

        return isset(ColorUtil::COLORS_NAMES[$data->value]) || (isset($data->colorType) && $data->colorType == 'hex');
    }

    public function match($type)
    {

        return $type == 'color';
    }

    public function render(array $options = [])
    {
        $index = spl_object_hash($this);
        $key = md5(json_encode($options));

        if (isset(static::$cache[$index][$key])) {

            return static::$cache[$index][$key];
        }

        if (isset($this->data->value)) {

            if ($this->data->value[0] == '#') {

                static::$cache[$index][$key] = $this->parseHexColor($this->data->value, $options);
            }

            else {

                static::$cache[$index][$key] = $this->parseNamedColor($this->data->value, $options);
            }
        }

        else {

            $callback = null;

            if ($this->data->name == 'rgb' || $this->data->name == 'rgba') {

                $callback = 'parseRGBColor';
            } else {

                $callback = 'parseHSLColor';
            }

            static::$cache[$index][$key] = call_user_func([$this, $callback], $this->data, $options);
        }

        return static::$cache[$index][$key];
    }

    protected function parseNamedColor($str, array $options)
    {

        $str = strtolower($str);

        if (isset(ColorUtil::COLORS_NAMES[$str])) {

            $name = ColorUtil::COLORS_NAMES[$str];

            $shortened = ColorUtil::shorten($name);

            if (strlen($str) > strlen($shortened)) {

                if ((strlen($shortened) == 5 || strlen($shortened) == 9) && empty($options['rgba_hex'])) {

                    return ColorUtil::hex2rgba($shortened, $options);
                }

                return $shortened;
            }

            if (strlen($str) > strlen($name)) {

                return $name;
            }
        }

        return ColorUtil::shorten($str);
    }

    public static function parseHexColor($str, array $options)
    {

        $color = ColorUtil::expand($str);
        $short = ColorUtil::shorten($color);
        $length = strlen($short);

        if (isset(ColorUtil::NAMES_COLORS[$color]) && $length > strlen(ColorUtil::NAMES_COLORS[$color])) {

            return ColorUtil::NAMES_COLORS[$color];
        }

        if (empty($options['rgba_hex'])) {

            if ($length == 5 || $length == 9) {

                return ColorUtil::hex2rgba($short, $options);
            }
        }

        return $short;
    }

    protected function shortestValue($color, $named_color)
    {

        return is_null($named_color) || strlen($named_color) > strlen($color) ? $color : $named_color;
    }

    public static function parseRGBColor($data, array $options)
    {

        $r = (string)$data->arguments->{0};
        $g = (string)$data->arguments->{2};
        $b = (string)$data->arguments->{4};
        $a = (string)$data->arguments->{6};

        if ($a === '' || $a == 1 || $a == '100%') {

            $a = null;
        }

        if (substr($r, -1) == '%') {

            $r = floatval($r) * 255 / 100;
        }

        if (substr($g, -1) == '%') {

            $g = floatval($g) * 255 / 100;
        }

        if (substr($b, -1) == '%') {

            $b = floatval($b) * 255 / 100;
        }

        if (!is_null($a)) {

            if (substr($a, -1) == '%') {

                $a = floatval($a) / 100;
            }
        }

        $hex = ColorUtil::rgba2hex($r, $g, $b, $a);
        $expanded = ColorUtil::expand($hex);

        $named_color = null;

        if (isset(ColorUtil::NAMES_COLORS[$expanded])) {

            $named_color = ColorUtil::NAMES_COLORS[$expanded];
        }

        if ($hex[0] != '#') {

            $named_color = $hex;
        }

        $length = strlen($hex);

        if (!empty($options['rgba_hex']) || ($length != 5 && $length != 9)) {

            return static::shortestValue($hex, $named_color);
        }

        $rgba = !empty($options['css_level']) && $options['css_level'] > 3 ? 'rgb' : 'rgba';

        if (!empty($options['compress'])) {

            $color = sprintf(is_null($a) ? 'rgb(%s,%s,%s)' : $rgba . '(%s,%s,%s,%s)', Number::compress($r), Number::compress($g), Number::compress($b), Number::compress($a));
        }

        else {

            $color = sprintf(is_null($a) ? 'rgb(%s, %s, %s)' : $rgba . '(%s, %s, %s, %s)', $r, $g, $b, $a);
        }

        return static::shortestValue($color, $named_color);
    }

    public static function parseHSLColor($data, array $options)
    {

        $h = $data->arguments->{0};
        $s = $data->arguments->{2};
        $l = $data->arguments->{4};
        $a = $data->arguments->{6};

        $alpha = (string)$a;

        if ($alpha === '' || $alpha == 1 || $alpha == '100%') {

            $a = null;
        }

        if (!is_null($a)) {

            if ($a->unit == '%') {

                $a = Number::compress($a->value / 100);
            } else {

                $a = $a->render($options);
            }
        }

     //   if (!empty($options['rgba_hex'])) {

     //   }

            $sa = floatval((string)$s->value) / 100;
            $li = floatval((string)$l->value) / 100;

            switch ($h->unit) {

                case 'rad':

                    $hu = floatval((string)$h->value) / (2 * pi());
                    break;

                case 'turn':
                    // do nothing
                    $hu = floatval((string)$h->value);
                    break;

                case 'deg':
                default:

                    $hu = floatval((string)$h->value) / 360;
                    break;
            }


        $hex = ColorUtil::hsl2hex($hu, $sa, $li, $a);
        $expanded = ColorUtil::expand($hex);

        $named_color = null;

        if (isset(ColorUtil::NAMES_COLORS[$expanded])) {

            $named_color = ColorUtil::NAMES_COLORS[$expanded];
        }

        if ($hex[0] != '#') {

            $named_color = $hex;
        }

        $length = strlen($hex);

        if (!empty($options['rgba_hex']) || ($length != 5 && $length != 9)) {

            return static::shortestValue($hex, $named_color);
        }

        if ((string)$h->unit == 'deg') {

            $h = (string)$h->value;
        } else {

            $h = (string)$h;
        }

        $l = $l->render($options);
        $s = $s->render($options);

        $hsla = !empty($options['css_level']) && $options['css_level'] > 3 ? 'hsl' : 'hsla';

        if (!empty($options['compress'])) {

            $color = sprintf(is_null($a) ? 'hsl(%s,%s,%s)' : $hsla . '(%s,%s,%s,%s)', Number::compress($h), $s, $l, Number::compress($a));
        }

        else {

            $color = sprintf(is_null($a) ? 'hsl(%s, %s, %s)' : $hsla . '(%s, %s, %s, %s)', $h, $s, $l, $a);
        }

        return static::shortestValue($color, $named_color);
    }
}
