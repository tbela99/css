<?php

namespace TBela\CSS\Element;

use Exception;
use \TBela\CSS\Interfaces\ElementInterface;
use TBela\CSS\Value;

class Rule extends RuleList
{

    /**
     * Return the css selectors
     * @return string[]
     */
    public function getSelector()
    {

        return $this->ast->selector;
    }

    protected function parseSelector($selectors)
    {

        if (is_array($selectors) && is_string($selectors[0])) {

            $selectors = implode(',', $selectors);
        }

        if (is_string($selectors)) {

            $selectors = Value::parse($selectors, null, true, '', '');
        }

        $selectors = Value::split(Value::renderTokens($selectors, ['omit_unit' => false]), ',');

        $result = [];

        foreach ($selectors as $selector) {

            $selector = trim($selector);
            $result[$selector] = $selector;
        }

        return array_values($result);
    }

    /**
     * Set css rule selector
     * @param string|array $selectors
     * @return $this
     */
    public function setSelector($selectors): static
	{

        $this->ast->selector = $this->parseSelector($selectors);
        return $this;
    }

    /**
     * Add css selectors
     * @param array|string $selector
     * @return $this
     */
    public function addSelector($selector): static
	{

        $result = [];

        foreach ($this->ast->selector as $r) {

            $result[trim($r)] = $r;
        }

        foreach ($this->parseSelector($selector) as $r) {

            $result[trim($r)] = $r;
        }

        $this->ast->selector = array_values($result);
        return $this;
    }

    /**
     * Remove a css selector
     * @param array|string $selector
     * @return Rule
     * @throws Exception
     */
    public function removeSelector($selector): static
	{

        if (!is_array($selector)) {

            $selector = array_map('trim', explode(',', $selector));
        }

        $selector = array_values(array_diff($this->ast->selector, $selector));

        if (empty($selector)) {

            throw new \Exception(sprintf('the selector is empty: %s:%s:%s', $this->ast->src ?? '', $this->ast->position->line ?? '', $this->ast->position->column ?? ''), 400);
        }

        $this->ast->selector = $selector;

        return $this;
    }

    /**
     * Add css declaration
     * @param string $name
     * @param string $value
     * @return Declaration
     * @throws Exception
     */
    public function addDeclaration(string $name, string $value): Declaration
	{

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
    public function merge(Rule $rule): static
	{

        $this->addSelector($rule->getSelector());

        foreach ($rule->getChildren() as $element) {

            $this->addDeclaration($element->getName(), $element->getValue());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function support(ElementInterface $child): bool
	{

        if ($child instanceof Comment) {

            return true;
        }

        return $child instanceof Declaration;
    }
}