<?php

namespace TBela\CSS;

use TBela\CSS\Event\Event;

class Traverser extends Event
{

    public const IGNORE_NODE = 1;
    public const IGNORE_CHILDREN = 2;

    protected function process(Element $node, array $data)
    {

        foreach ($data as $res) {

            if ($res === static::IGNORE_NODE) {

                return static::IGNORE_NODE;
            }

            if ($res === static::IGNORE_CHILDREN) {

                return static::IGNORE_CHILDREN;
            }

            if ($res instanceof Element) {

                if ($res !== $node) {

                    return $res;
                } else {

                    $result = $res;
                    break;
                }
            }
        }

        return $node;
    }

    public function traverse(Element $element) {

        $result = $this->doTraverse($element);

        if (!($result instanceof Element)) {

            return null;
        }

        return $result;
    }

    protected function doTraverse(Element $node)
    {

        $result = $this->process($node, $this->emit('enter', $node));

        if ($result === static::IGNORE_NODE) {

            return static::IGNORE_NODE;
        }

        $ignore_children = $result === static::IGNORE_CHILDREN;

        if ($result instanceof Element) {

            if ($result !== $node) {

                $node = $result;
            }
        }

        if ($node === func_get_arg(0) && $node instanceof RuleList) {

            $children = $node['children'];
            $node = clone $node;
            $node->removeChildren();

            if ($ignore_children) {

                return $node;
            }

            foreach ($children as $child) {

                $temp_c = $this->doTraverse($child);

                if ($temp_c instanceof Element) {

                    $node->append($temp_c);
                } else if ($temp_c !== static::IGNORE_NODE) {

                    $node->append($child);
                }
            }
        }


        $result = $this->process($node, $this->emit('exit', $node));

        if ($result === static::IGNORE_NODE) {

            return static::IGNORE_NODE;
        }

        $ignore_children = $result === static::IGNORE_CHILDREN;

        if ($result instanceof Element) {

            if ($result !== $node) {

                $node = $result;
            }
        }

        return $node;
    }
}