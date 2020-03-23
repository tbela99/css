<?php

namespace TBela\CSS;

use ArrayIterator;
use Exception;
use InvalidArgumentException;
use TBela\CSS\Element\Comment;
use TBela\CSS\Element\Stylesheet;
use Traversable;
use function in_array;

/**
 * Class Elements
 * @package TBela\CSS
 */
abstract class Elements extends Element implements RuleList
{

    /**
     * create child nodes
     * @ignore
     */
    protected function createElements()
    {

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
    public function addComment($value)
    {

        $comment = new Comment();
        $comment->setValue('/* ' . $value . ' */');

        return $this->append($comment);
    }

    /**
     * @inheritDoc
     */
    public function hasChildren()
    {

        return isset($this->ast->elements) && count($this->ast->elements) > 0;
    }

    /**
     * @inheritDoc
     */
    public function removeChildren()
    {

        if (is_callable([$this, 'isLeaf']) && call_user_func([$this, 'isLeaf'])) {

            return $this;
        }

        if (isset($this->ast->elements)) {

            foreach ($this->ast->elements as $element) {

                if (!is_null($element->parent)) {

                    $element->parent->remove($element);
                }
            }

            $this->ast->elements = [];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {

        return isset($this->ast->elements) ? $this->ast->elements : [];
    }

    /**
     * @inheritDoc
     */
    public function support(Element $child)
    {

        $element = $this;

        // should not append a parent as a child
        while ($element) {

            if ($element === $child) {

                return false;
            }

            $element = $element['parent'];
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function append(Element $element)
    {

        if (!$this->support($element)) {

            throw new InvalidArgumentException('Illegal argument', 400);
        }

        if ($element instanceof Stylesheet) {

            foreach ($element->getChildren() as $child) {

                if (!$this->support($child)) {

                    throw new InvalidArgumentException('Illegal argument', 400);
                }

                $child->parent->remove($child);
                $this->append($child);
            }
        } else {

            if (!in_array($element, $this->ast->elements, true)) {

                if (!empty($element->parent)) {

                    $element->parent->remove($element);
                }

                $this->ast->elements[] = $element;
                $element->parent = $this;
            }
        }

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function insert(Element $element, $position)
    {
        if (!$this->support($element) || $position < 0) {

            throw new InvalidArgumentException('Illegal argument', 400);
        }

        $parent = $element->parent;

        if (isset($this->ast->elements)) {

            if (!is_null($parent)) {

                $parent->remove($element);
            }

            $position = min($position, count($this->ast->elements));

            array_splice($this->ast->elements, $position, 0, [$element]);

            $element->parent = $this;
        }

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function remove(Element $element)
    {

        if ($element->getParent() === $this) {

            $index = array_search($element, $this->ast->elements);

            if ($index !== false) {

                array_splice($this->ast->elements, $index, 1);
            }
        }

        return $element;
    }

    /**
     * return an iterator of child nodes
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {

        return new ArrayIterator(isset($this->ast->elements) ? $this->ast->elements : []);
    }
}