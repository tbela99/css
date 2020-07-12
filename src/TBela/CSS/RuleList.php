<?php

namespace TBela\CSS;

use ArrayIterator;
use InvalidArgumentException;
use TBela\CSS\Element\Comment;
use TBela\CSS\Element\Stylesheet;
use TBela\CSS\Interfaces\RuleListInterface;
use Traversable;
use function in_array;

/**
 * Class Elements
 * @package TBela\CSS
 */
abstract class RuleList extends Element implements RuleListInterface
{
    public function __construct($ast = null, RuleListInterface $parent = null)
    {
        parent::__construct($ast, $parent);
        $this->createChildren();
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
     * @param int $offset
     * @param int|null $length
     * @param ...Element|null $replacement
     * @return Element[]
     */
    public function splice($offset, $length = null, $replacement = null) {

        if(!empty($this->ast->isLeaf)) {

            throw new \Exception();
        }

        $args = [&$this->ast->children, $offset];

        if (!is_null($length)) {

            $args[] = $length;

            if (!is_null($replacement)) {

                $args[] = $replacement;
            }
        }

        foreach ($this->ast->children as $child) {

            $child->parent = $this;
        }

        return array_map(function ($element) {

            $element->parent = null;

            return $element;

        }, call_user_func_array('array_splice', $args));
    }

    /**
     * create child nodes
     * @ignore
     */
    protected function createChildren()
    {

        if (!isset($this->ast->children)) {

            $this->ast->children = [];
        }

        foreach ($this->ast->children as $key => $value) {

            $this->ast->children[$key] = Element::getInstance($this->ast->children[$key]);
            $this->ast->children[$key]->parent = $this;
        }
    }

    /**
     * @inheritDoc
     */
    public function hasChildren()
    {

        return isset($this->ast->children) && count($this->ast->children) > 0;
    }

    /**
     * @inheritDoc
     */
    public function removeChildren()
    {

        if (!empty($this->ast->isLeaf)) {

            return $this;
        }

        if (isset($this->ast->children)) {

            foreach ($this->ast->children as $element) {

                if (!is_null($element->parent)) {

                    $element->parent->remove($element);
                }
            }

            $this->ast->children = [];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {

        return isset($this->ast->children) ? $this->ast->children : [];
    }

    public function setChildren(array $elements) {

        $this->ast->children = [];

        foreach ($elements as $child) {

            $this->append($child instanceof Element ? $child : Element::getInstance($child));
        }

        return $this;
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
    public function append(Element ...$elements)
    {

        foreach ($elements as $element) {

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

                if (empty($this->ast->children) || !in_array($element, $this->ast->children, true)) {

                    if (!empty($element->parent)) {

                        $element->parent->remove($element);
                    }

                    $this->ast->children[] = $element;
                    $element->parent = $this;
                }
            }
        }

        return func_num_args() == 1 ? $elements[0] : $elements;
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

        if (isset($this->ast->children)) {

            if (!is_null($parent)) {

                $parent->remove($element);
            }

            $position = min($position, count($this->ast->children));

            array_splice($this->ast->children, $position, 0, [$element]);

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

            $index = array_search($element, $this->ast->children);

            if ($index !== false) {

                array_splice($this->ast->children, $index, 1);
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

        return new ArrayIterator(isset($this->ast->children) ? $this->ast->children : []);
    }
}