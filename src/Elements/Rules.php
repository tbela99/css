<?php 

namespace TBela\CSS\Elements;

use Exception;
use InvalidArgumentException;
use TBela\CSS\Elements;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Rule;

/**
 * Rules container
 * @package TBela\CSS
 */
class Rules extends Elements {

    /**
     * @param string $name
     * @param string|null $value
     * @param int $type the type of the node:
     * - Rule::ELEMENT_AT_RULE_LIST (the elements can contain other rules)
     * - Rule::ELEMENT_AT_DECLARATIONS_LIST the element contains declarations
     * - Rule::ELEMENT_AT_NO_LIST the element does not support children
     * @return AtRule
     * @throws Exception
     */
    public function addAtRule($name, $value = null, $type = 0) {

        $rule = new AtRule();

        if ($type < 0 || $type > 2) {

            throw new InvalidArgumentException('Illegal rule type: '.$type);
        }

        switch ($type) {

            case AtRule::ELEMENT_AT_RULE_LIST:

                break;
            case AtRule::ELEMENT_AT_DECLARATIONS_LIST:

                $rule->ast->hasDeclarations = true;
                break;

            case AtRule::ELEMENT_AT_NO_LIST:

                $rule->ast->isLeaf = true;
                break;
        }

        $rule['name'] = $name;
        $rule['value'] = $value;

        return $this->append($rule);
    }

    /**
     * @param $selectors
     * @return Rule
     * @throws Exception
     */
    public function addRule ($selectors) {

        $rule = new Rule();
        $rule['selector'] = $selectors;

        return $this->append($rule);
    }
}