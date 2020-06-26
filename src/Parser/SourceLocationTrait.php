<?php

namespace TBela\CSS\Parser;

trait SourceLocationTrait
{

    public function __get($name) {

        if (method_exists($this, 'get'.$name)) {

            return $this->{'get'.$name}();
        }
    }

    public function __set($name, $value) {

        if (method_exists($this, 'set'.$name)) {

            return $this->{'set'.$name}($value);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}