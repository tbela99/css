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

        return $this->unique($result);
    }
}