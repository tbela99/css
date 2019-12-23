<?php 

namespace TBela\CSS;
use Exception;
use InvalidArgumentException;

/**
 * Rules container
 * @package TBela\CSS
 */
class ElementsRule extends Elements {

    const ELEMENT_AT_RULE_LIST = 0;
    const ELEMENT_AT_DECLARATIONS_LIST = 1;
    const ELEMENT_AT_NO_LIST = 2;

    /**
     * @param string $name
     * @param string|null $value
     * @param int $type the type of the node:
     * - ElementsRule::ELEMENT_AT_RULE_LIST (the elements can contain other rules)
     * - ElementsRule::ELEMENT_AT_DECLARATIONS_LIST the element contains declarations
     * - ElementsRule::ELEMENT_AT_NO_LIST the element does not support children
     * @return Element
     * @throws Exception
     */
    public function addAtRule($name, $value = null, $type = 0) {

        $rule = new ElementAtRule();

        if ($type < 0 || $type > 2) {

            throw new InvalidArgumentException('Illegal rule type: '.$type);
        }

        switch ($type) {

            case static::ELEMENT_AT_RULE_LIST:

                break;
            case static::ELEMENT_AT_DECLARATIONS_LIST:

                $rule->ast->hasDeclarations = true;
                break;

            case static::ELEMENT_AT_NO_LIST:

                $rule->ast->isLeaf = true;
                break;
        }

        $rule['name'] = $name;
        $rule['value'] = $value;

        return $this->append($rule);
    }

    /**
     * @param $selectors
     * @return ElementRule
     * @throws Exception
     */
    public function addRule ($selectors) {

        $rule = new ElementRule();
        $rule['selector'] = $selectors;

        return $this->append($rule);
    }
}