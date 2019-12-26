<?php 

namespace TBela\CSS;

use Exception;

class ElementAtRule extends ElementsRule {

    /**
     * Type of @at-rule that contains other rules like @media
     */
    const ELEMENT_AT_RULE_LIST = 0;
    /**
     * Type of @at-rule that contains declarations @viewport
     */
    const ELEMENT_AT_DECLARATIONS_LIST = 1;
    /**
     * Type of @at-rule that contains no child like @namespace
     */
    const ELEMENT_AT_NO_LIST = 2;

    use ElementTrait;

    public function isLeaf () {

        return !empty($this->ast->isLeaf);
    }

    public function hasDeclarations () {

        return !empty($this->ast->hasDeclarations);
    }

    /**
     * @inheritDoc
     */
    public function support (Element $child) {

        if (!empty($this->ast->isLeaf)) {

            return false;
        }

        if ($child instanceof  ElementComment) {

            return true;
        }

        if (!empty($this->ast->hasDeclarations)) {

            if (!($child instanceof ElementDeclaration)) {

                return false;
            }
        }

        else {

            if ($child instanceof ElementDeclaration) {

                return false;
            }
        }

        return parent::support($child);
    }

    /**
     * @param string $name
     * @param string $value
     * @return ElementDeclaration
     * @throws Exception
     */
    public function addDeclaration ($name, $value) {

        $declaration = new ElementDeclaration();

        $declaration['name'] = $name;
        $declaration['value'] = $value;

        return $this->append($declaration);
    }

}