<?php


namespace TBela\CSS\Property;

trait PropertyTrait
{

    /**
     * @var null|string
     * @ignore
     */
    protected $src = null;

    /**
     * @param $src
     * @return $this
     */
    public function setSrc($src) {

        $this->src = $src;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSrc() {

        return $this->src;
    }
}