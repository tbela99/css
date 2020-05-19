<?php 

namespace TBela\CSS;

use Exception;

/**
 * Css node methods
 * @package TBela\CSS
 */
trait ElementTrait  {

    /**
     * get css node name
     * @param bool $getVendor
     * @return string
     * @throws Exception
     */
    public function getName(bool $getVendor = true): string {

        $vendor = $this->getVendor();

        if ($getVendor && $vendor !== '') {

            return '-'.$vendor.'-'.$this->ast->name;
        }

        return $this->ast->name;
    }

    /**
     * set vendor prefix
     * @return string
     */
    public function getVendor () : string {

        if (isset($this->ast->vendor)) {

            return $this->ast->vendor;
        }

        return '';
    }

    /**
     * get node name
     * @param string $name
     * @return Element
     */
    public function setName (string $name) {

        if (preg_match('/^(-([a-zA-Z]+)-(\S+))/', trim($name), $match)) {

            $this->ast->vendor =  $match[2];
            $this->ast->name = $match[3];
        }

        else {

            $this->ast->name = $name;
        }

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return $this
     */
    public function setVendor (string $prefix) {

        if (is_null($prefix) || $prefix === '') {

            unset($this->ast->vendor);
        }

        else {

            $this->ast->vendor = $prefix;
        }

        return $this;
    }
}