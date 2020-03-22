<?php 

namespace TBela\CSS;

use Exception;
use IteratorAggregate;

/**
 * Interface implemented by rules containers
 * @package TBela\CSS
 * @property-read array childNodes. Return the child nodes. Accessed with array-like syntax $element['childNodes']
 * @property-read Element|null firstChild. Return the first child. Accessed with array-like syntax $element['firstChild']
 * @property-read Element|null lastChild. Return the last child. Accessed with array-like syntax $element['lastChild']
 */
interface RuleList extends IteratorAggregate {

    /**
     * return true if the node has children
     * @return bool
     */
    public function hasChildren();
    /**
     * return child nodes
     * @return array
     */
    public function getChildren();

    /**
     * Add a comment node
     * @param string $value
     * @return Element\Comment
     */
    public function addComment ($value);

    /**
     * check if this node accepts element as a child
     * @param Element $child
     * @return bool
     */
    public function support (Element $child);

    /**
     * append child node
     * @param Element $element
     * @return Element
     * @throws Exception
     */
    public function append(Element $element);

    /**
     * insert a child node at the specified position
     * @param Element $element
     * @param int $position
     * @return Element
     * @throws Exception
     */
    public function insert(Element $element, $position);

    /**
     * remove a child node from its parent
     * @param Element $element
     * @return Element
     */
    public function remove (Element $element);

    /**
     * Remove all children
     * @return Element
     */
    public function removeChildren();
}