<?php 

namespace TBela\CSS;

class ElementAtRule extends ElementsRule {

    use ElementTrait;

    public function isLeaf () {

        return !empty($this->ast->isLeaf);
    }

    public function hasDeclarations () {

        return !empty($this->ast->hasDeclarations);
    }
}