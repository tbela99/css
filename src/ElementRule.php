<?php 

namespace TBela\CSS;

use Exception;

class ElementRule extends Elements {

    /**
     * @return array
     */
    public function getSelector () {

        return $this->ast->selectors;
    }

    /**
     * @param string|array $selectors
     * @return $this
     */
    public function setSelector ($selectors) {

        if (!is_array($selectors)) {

            $selectors = array_map('trim', explode(',', $selectors));
        }

        $this->ast->selectors = $selectors;

        return $this;
    }

    /**
     * @param aray|string $selector
     * @return $this
     */
    public function addSelector($selector) {

        if (!is_array($selector)) {

            $selector = array_map('trim', explode(',', $selector));
        }

        if (!isset($this->ast->selectors)) {

            $this->ast->selectors = [];
        }

        array_splice($this->ast->selectors, count($this->ast->selectors), 0, $selector);

        return $this;
    }

    /**
     * @param array|string $selector
     * @return $this
     */
    public function removeSelector($selector) {

        if (!is_array($selector)) {

            $selector = array_map('trim', explode(',', $selector));
        }

        $this->ast->selectors = array_diff($this->ast->selectors, $selector);
        return $this;
    }


    /**
     * @param string $name
     * @param string $value
     * @return ElementDeclaration
     */
    public function addDeclaration ($name, $value) {

        $declaration = new ElementDeclaration();

        $declaration['name'] = $name;
        $declaration['value'] = $value;

        return $this->append($declaration);
    }

    /**
     * Merge the specified declaration
     * @param ElementRule $rule
     */
    public function merge (ElementRule $rule) {

        $this->addSelector($rule['selector']);

        foreach ($rule['children'] as $element) {

            $this->addDeclaration($element['name'], $element['value']);
        }

        return $this;
    }
}