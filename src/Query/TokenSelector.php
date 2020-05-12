<?php

namespace TBela\CSS\Query;

use Exception;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Rule;
use TBela\CSS\RuleList;

class TokenSelector extends Token implements TokenSelectorInterface
{
 //   protected string $node = '';
//    protected ?string $context = null;

    /**
     * @var TokenSelectorValueInterface[][]
     */
    protected array $value = [];

    public function __construct($data)
    {
        parent::__construct($data);

        $index = 0;

        $this->value = [];

        foreach ($data->value as $value) {

            if ($value->type == 'separator') {

                ++$index;
                continue;
            }

            $this->value[$index][] = call_user_func([TokenSelectorValue::class, 'getInstance'], $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function filter(array $context): array
    {

        $result = [];



        /**
         * @var TokenSelectorInterface $filter
         */
        foreach ($this->value as $group) {

            $tmp = $context;

            // must pass at least all filter for one group
            foreach ($group as $filter) {

                $tmp = $filter->evaluate($tmp);

                if (empty($tmp)) {

                    continue 2;
                }
            }

            array_splice($result, count($result), 0, $tmp);
        }

        return $this->sortContext($this->unique($result));

        foreach ($this->value as $filter) {

            if (empty($context)) {

                break;
            }

            continue;

            foreach ($context as $key => $element) {

                // match a css selector
                if ($filter->type == 'string') {

                    if ($element instanceof Rule) {

                        // check this ...
                        if (!(implode(',', $element->getSelector()) == $filter->value)) {

                            //   var_export(['selector' => $element->getSelector(), 'filter' => $filter->value]);

                            unset($context[$key]);
                        }
                    } else if ($element instanceof AtRule) {

                        if (!('@' . $element->getName() == $filter->value)) {

                            //     var_export(['name' => $element->getName(), 'filter' => $filter->value]);

                            unset($context[$key]);
                        }
                    } else {

                        if (!($element->getName() == $filter->value)) {

                            //   var_export(['name' => $element->getName(), 'filter' => $filter->value]);
                            unset($context[$key]);
                        }
                    }
                }
                // match [@name] or [0] or [function()]
                else if ($filter->type == 'attribute' && count($filter->value) == 1) {

                    //   var_dump(array_map('trim', $context));

                    //   var_dump($filter->value[0]->value);

                    if ($filter->value[0]->type == 'index') {

                        $context = isset($context[$filter->value[0]->value]) ? [$context[$filter->value[0]->value]] : [];
                    }

                    else if ($filter->value[0]->type == 'function') {

                        if ($filter->value[0]->name == 'contains') {

                            if (count($filter->value[0]->value) != 3) {

                                throw new Exception(sprintf('Function %s expected %d arguments: ' . var_export($filter, true), 'contains', 3), 400);
                            }

                            if ($filter->value[0]->value[0]->type != 'attribute_name') {

                                throw new Exception(sprintf('Function %s expected parameter %d of type %s: ' . var_export($filter, true), 'contains', 1, 'attribute_name'), 400);
                            }

                            if ($filter->value[0]->value[1]->type != 'separator' || $filter->value[0]->value[1]->value != ',') {

                                throw new Exception(sprintf('Function %s expect %s  after : ' . var_export($filter, true), 'contains', ',', 'attribute_name'), 400);
                            }

                            $attr = $element[$filter->value[0]->value[1]->value];

                            if (is_null($attr) || strpos($attr, $filter->value[0]->value[2]->value) !== false) {

                                unset($context[$key]);
                            }
                        }
                        else {

                            throw new Exception('Function not implemented: ' . var_export($filter, true), 400);
                        }

                        $context = isset($context[$filter->value[0]->value]) ? [$context[$filter->value[0]->value]] : [];
                    } else {

                        throw new Exception('Not implemented: ' . var_export($filter, true), 400);
                    }

                    if (empty($context)) {

                        return [];
                    }
                }

                else if ($filter->type == 'attribute' && count($filter->value) == 3) {

                    throw new Exception('Not implemented: ' . var_export($filter, true), 400);

                } else {

                    throw new Exception('Not implemented: ' . var_export($filter, true), 400);
                }
            }
        }

        return $context;
    }
}