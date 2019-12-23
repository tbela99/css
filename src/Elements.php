<?php 

namespace TBela\CSS;

use ArrayIterator;
use function in_array;

class Elements extends Element implements RuleList  {

    protected function createElements () {

        if (!isset($this->ast->elements)) {

            $this->ast->elements = [];
        }

        foreach ($this->ast->elements as $key => $value) {

            $element = Element::getInstance($this->ast->elements[$key]);
            $element->parent = $this;
            $this->ast->elements[$key] = $element;

        }
    }

    /**
     * @inheritDoc
     */
    public function hasChildren() {

        return isset($this->ast->elements) && count($this->ast->elements)  > 0;
    }

    /**
     * @inheritDoc
     */
    public function removeChildren() {

        if (is_callable([$this, 'isLeaf']) && call_user_func([$this, 'isLeaf'])) {

            return $this;
        }

        if (isset($this->ast->elements)) {

            foreach ($this->ast->elements as $element) {

                $element->setParent(null);
            }

            $this->ast->elements = [];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren () {

        return isset($this->ast->elements) ? $this->ast->elements : [];
    }

    /**
     * @inheritDoc
     */
    public function append (Element $element) {

        if ($this != $element && $this != $element->getParent()) {

            if (($this instanceof ElementStylesheet) && ($element instanceof ElementStylesheet)) {

                foreach ($element->getChildren() as $child) {

                    if (!empty($child->parent)) {

                        $child->parent->remove($child);
                    }

                    $this->append($child);
                }
            }

            else {

                if (!in_array($element, $this->ast->elements)) {

                    if (!empty($element->parent)) {

                        $element->parent->remove($element);
                    }

                    $this->ast->elements[] = $element;
                    $element->parent = $this;
                }
            }
        }

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function insert(Element $element, $position)
    {
        $parent = $element->parent;

        if (isset($this->ast->elements)) {

            if (!is_null($parent)) {

                $parent->remove($element);
            }

            $position = min ($position, count($this->ast->elements));

            array_splice($this->ast->elements, $position, 0, [$element]);

            $element->parent = $this;
        }

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function remove (Element $element) {

       if ($element->getParent() == $this) {

        $index = array_search ($element, $this->ast->elements);

        if ($index !== false) {

            array_splice ($this->ast->elements, $index, 1);
        }
       }
       
       return $element;
    }

    public function getIterator () {

        return new ArrayIterator(isset($this->ast->elements) ? $this->ast->elements : []);
    }
}