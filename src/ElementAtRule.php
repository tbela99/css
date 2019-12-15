<?php 

namespace TBela\CSS;

class ElementAtRule extends Elements implements RuleList {

    use ElementTrait;

    public function isLeaf () {

        return !empty($this->ast->isLeaf);
    }

    public function hasDeclarations () {

        return !empty($this->ast->hasDeclarations);
    }
}