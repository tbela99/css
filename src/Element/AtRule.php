<?php 

namespace TBela\CSS\Element;

use Exception;
use TBela\CSS\Element;
use TBela\CSS\Element\Comment;
use TBela\CSS\Element\Declaration;
use TBela\CSS\ElementTrait;
use TBela\CSS\Elements\Rules;

class AtRule extends Rules {

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

        if ($child instanceof Comment) {

            return true;
        }

        if (!empty($this->ast->hasDeclarations)) {

            if (!($child instanceof Declaration)) {

                return false;
            }
        }

        else {

            if ($child instanceof Declaration) {

                return false;
            }
        }

        return parent::support($child);
    }

    /**
     * @param string $name
     * @param string $value
     * @return Declaration
     * @throws Exception
     */
    public function addDeclaration ($name, $value) {

        $declaration = new Declaration();

        $declaration['name'] = $name;
        $declaration['value'] = $value;

        return $this->append($declaration);
    }
}