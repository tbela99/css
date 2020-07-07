<?php 

namespace TBela\CSS\Element;

use Exception;
use TBela\CSS\Element;
use TBela\CSS\RuleList;
use TBela\CSS\Value;

class Rule extends RuleList {

    /**
     * Return the css selectors
     * @return array
     */
    public function getSelector () {

        return $this->ast->selector;
    }

    /**
     * Set css rule selector
     * @param string|array $selectors
     * @return $this
     */
    public function setSelector ($selectors) {

        if (!is_array($selectors)) {

            $selectors = Value::parse(array_unique($selectors))->split(',');
        }

        $unique = [];

        foreach ($selectors as $selector) {

            if (is_string($selector)) {

                $selector = Value::parse($selector);
            }

            $unique[trim($selector->render(['remove_comments' => true]))] = $selector;
        }

        $this->ast->selector = array_values($unique);

        return $this;
    }

    /**
     * Add css selectors
     * @param array|string $selector
     * @return $this
     */
    public function addSelector($selector) {

        if (!is_array($selector)) {

            $selector = array_map('trim', explode(',', $selector));
        }

        if (!isset($this->ast->selector)) {

            $this->ast->selector = [];
        }

        array_splice($this->ast->selector, count($this->ast->selector), 0, $selector);

        $this->ast->selector = array_unique($this->ast->selector);

        return $this;
    }

    /**
     * Remove a css selector
     * @param array|string $selector
     * @return $this
     */
    public function removeSelector($selector) {

        if (!is_array($selector)) {

            $selector = array_map('trim', explode(',', $selector));
        }

        $this->ast->selector = array_diff($this->ast->selector, $selector);
        return $this;
    }

    /**
     * Add css declaration
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

    /**
     * Merge another css rule into this
     * @param Rule $rule
     * @return Rule $this
     * @throws Exception
     */
    public function merge (Rule $rule) {

        $this->addSelector($rule->getSelector());

        foreach ($rule->getChildren() as $element) {

            $this->addDeclaration($element->getName(), $element->getValue());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function support (Element $child) {

        if ($child instanceof Comment) {

            return true;
        }

        return $child instanceof Declaration;
    }
}