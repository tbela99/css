<?php

namespace TBela\CSS;

/**
 * remove white space and empty declarations for now
 * @todo optimize css properties
 * - remove unit from 0
 * - optimize color
 * - compute short-hand properties? (color, background, border, border-radius, etc)?
 */

/**
 * Print minified CSS
 * @package CSS
 */
class Compress extends Identity
{

    public function __construct(array $options = [])
    {

        parent::__construct($options);

        $this->glue = '';
        $this->indent = '';
        $this->charset = true;
        $this->remove_comments = true;
        $this->remove_empty_nodes = true;
    }

    protected function renderValue(Element $element)
    {

        $value = $element['value'];
        $type = $element['type'];

        $value = $this->filter->value($value, $element);

        if ($type == 'declaration') {

            $value = $this->filter->color($value, $element);
        }

        // hash quoted words
        $hash = $this->escape($value);
        $value = $hash[0];

        // parse numbers
        $value = $this->filter->numbers($value);

        // remove unnecessary space
        $value = $this->filter->whitespace($value);

        $value = $this->unescape($value, $hash[1]);

        return trim($value);
    }

    /**
     * @param ElementDeclaration $element
     * @return string
     */
    protected function renderDeclaration(ElementDeclaration $element)
    {

        $name = $element->getName(false);

        $value = $this->renderValue($element);

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

                    if (count($value) == 3) {

                        if ($value[0] == $value[2]) {

                            unset ($value[2]);
                        }
                    }

                    if (count($value) == 2) {

                        if ($value[0] == $value[1]) {

                            unset ($value[1]);
                        }
                    }
                }

                $value = implode(' ', $value);
                break;
        }

        return $this->renderName($element) . ':' . $this->indent . $value;
    }
}

