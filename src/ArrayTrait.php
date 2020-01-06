<?php 

namespace TBela\CSS;

trait ArrayTrait  {

    public function offsetSet($offset, $value) {

        if (is_callable([$this, 'set'.$offset])) {

            call_user_func([$this, 'set'.$offset], $value);
        }
    }

    public function offsetExists($offset) {
        return is_callable([$this, 'get'.$offset]) || is_callable([$this, 'set'.$offset]);
    }

    public function offsetUnset($offset) {

        if (is_callable([$this, 'set'.$offset])) {

            call_user_func([$this, 'set'.$offset], null);
        }
    }

    public function offsetGet($offset) {

        return is_callable([$this, 'get'.$offset]) ? call_user_func([$this, 'get'.$offset]): null;
    }
}