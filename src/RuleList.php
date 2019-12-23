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