<?php

namespace TBela\CSS\Query;

class TokenSelector extends Token implements TokenSelectorInterface
{

    /**
     * @var TokenSelectorValueInterface[][]
     */
    protected $value = [];

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
    public function filter(array $context)
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