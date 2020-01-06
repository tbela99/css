<?php 

namespace TBela\CSS;

use Exception;
use IteratorAggregate;

interface RuleList extends IteratorAggregate {

    /**
     * @return bool
     */
    public function hasChildren();
    /**
     * @return array
     */
    public function getChildren();

    /**
     * @param string $value
     * @return Comment
     */
    public function addComment ($value);

    /**
     * check if this node accepts element as a child
     * @param Element $child
     * @return bool
     */
    public function support (Element $child);

    /**
     * @param Element $element
     * @return Element
     * @throws Exception
     */
    public function append(Element $element);

    /**
     * @param Element $element
     * @param int $position
     * @return Element
     * @throws Exception
     */
    public function insert(Element $element, $position);

    /**
     * @param Element $element
     * @return Element
     */
    public function remove (Element $element);

    /**
     * @return Element
     */
    public function removeChildren();
}