<?php

namespace TBela\CSS\Query;

use Exception;
use InvalidArgumentException;

trait TokenTrait
{
    protected string $type = '';

    protected function __construct($data)
    {

        foreach ($data as $key => $value) {

            if (!property_exists($this, $key)) {

                throw new InvalidArgumentException(sprintf('unknown property %s', $key), 400);
            }

            $this->{$key} = $value;
        }
    }

    public function __get($name) {

        switch ($name) {

            case 'type':

                return $this->{$name};
        }

        return null;
    }

    public static function getInstance($data) {

        if (!isset($data->type)) {

            throw new InvalidArgumentException(sprintf('invalid token %s', var_export($data, true)), 400);
        }

        $className = static::class.preg_replace_callback('#(^|-)([a-zA-Z])#', function ($matches) {

                return strtoupper($matches[2]);
        }, $data->type);

        if (!class_exists($className)) {

            throw new Exception(sprintf('class not found "%s"', $className));
        }

        return new $className($data);
    }

    protected function unique(array $context) {

        $result = [];

        foreach ($context as $element) {

            $result[spl_object_id($element)] = $element;
        }

        return array_values($result);
    }

    protected function sortContext(array $context): array {

        if (count($context) < 2) {

            return $context;
        }

        return $context;

        $info = [];

        /**
         * @var \TBela\CSS\Element $element
         */
        foreach ($context as $key => $element) {

            $index = spl_object_id($element);

            if (!isset($info[$index])) {

                $info[$index] = [
                    'key' => $key,
                    'number' => [],
                    'name' => is_null($element['name']) ? implode(',', (array) $element['selector']) : $element['name'],
                    'val' => (string) $element
                ];

                $el = $element;

                while ($el && ($parent = $el->getParent())) {

                    $info[$index]['number'][] = array_search($el, $parent->getChildren(), true);
                    $el = $parent;
                }

                $info[$index]['number'] = implode('', array_reverse($info[$index]['number']));
            }
        }

        \usort($info, function ($a, $b) {

            if ($a < $b) {

                return -1;
            }

            if ($a > $b) {

                return 1;
            }

            return 0;
        });

        $result = [];

        foreach ($info as $value) {

            $result[] = $context[$value['key']];
        }

        return $result;
    }
}