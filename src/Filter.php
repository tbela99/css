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
     * @param Rendererable $element
     * @return string
     */
    public function value ($value, Rendererable $element) {

        $type = (string) $element['type'];

        if ($type == 'AtRule') {

            if ($value !== '' && (string) $element['name'] == 'import') {

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
}
