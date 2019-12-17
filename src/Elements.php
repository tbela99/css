<?php 

namespace TBela\CSS;

class Elements extends Element implements \IteratorAggregate  {

//    protected $elements = [];

    protected function createElements () {

        if (!isset($this->ast->elements)) {

            $this->ast->elements = [];
        }

        foreach ($this->ast->elements as $key => $value) {

            $element = Element::getInstance($this->ast->elements[$key]);
            $element->setParent($this);
            $this->ast->elements[$key] = $element;

        }
    }

    public function hasChildren() {

        return isset($this->ast->elements) && count($this->ast->elements)  > 0;
    }

    public function getChildren () {

        return isset($this->ast->elements) ? $this->ast->elements : [];
    }

    public function getIterator () {

        return new \ArrayIterator(isset($this->ast->elements) ? $this->ast->elements : []);
    }

    public function append (Element $element) {

        if ($this != $element->getParent()) {

            $element->setParent($this);

            if (!\in_array($element, $this->elements)) {

                $this->ast->elements[] = $element;
            }  
        }

        return $element;
    }

    public function remove (Element $element) {

       if ($element->getParent() == $this) {

        $index = array_search ($element, $this->ast->elements);

        if ($index !== false) {

            array_splice ($this->ast->elements, $index, 1);
        }
       }
       
       return $element;
    }
}