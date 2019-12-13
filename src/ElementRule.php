<?php 

namespace TBela\CSS;

class ElementRule extends Elements {

    public function getSelector () {

        return $this->ast->selectors;
    }
}