<?php 

namespace TBela\CSS;

interface Renderer {

    /**
     * @param Element $element render element
     * @param null|int $level
     * @param bool $parents render parent nodes
     * @return string
     */
    public function render (Element $element, $level = null, $parents = false);
}