<?php

namespace TBela\CSS\Value;

use TBela\CSS\Color as ColorUtil;
use TBela\CSS\Value;

class Color extends Value
{

    protected static function validate($data)
    {

        if (isset($data->name) && isset($data->arguments)) {

            return in_array($data->name, ['rgb', 'rgba', 'hsl', 'hsla']);
        }

        return isset($data->value) && (isset(ColorUtil::COLORS_NAMES[$data->value]) || preg_match('#^' . ColorUtil::COLOR_HEX . '$#', $data->value));
    }

    public function match ($type) {

        return $type == 'color';
    }

    public function render($compress = false, array $options = [])
    {

        $rgba_hex = !empty($options['rgba_hex']) && $compress;

        if (isset($this->data->value)) {

            if ($this->data->value[0] == '#') {

                return ColorUtil::parseHexColor($this->data->value, $rgba_hex);
            }
            return ColorUtil::parseNamedColor($this->data->value, $rgba_hex);
        }

        $callback = null;

        if ($this->data->name == 'rgb' || $this->data->name == 'rgba') {

            $callback = 'parseRGBColor';
        } else {

            $callback = 'parseHSLColor';
        }

        return call_user_func([ColorUtil::class, $callback], $this->data->name . '(' . $this->data->arguments->render($compress, $options). ')', $rgba_hex);

    }
}
