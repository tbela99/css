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
    public function getName($getVendor = true) {

        $vendor = $this->getVendor();

        if ($getVendor && $vendor !== '') {

            return '-'.$vendor.'-'.$this->ast->name;
        }

        return (string) $this->ast->name;
    }

    /**
     * set vendor prefix
     * @return string
     */
    public function getVendor () {

        if (isset($this->ast->vendor)) {

            return (string) $this->ast->vendor;
        }

        return '';
    }

    /**
     * get node name
     * @return string
     */
    public function setName ($name) {

        if (preg_match('/^(-([a-zA-Z]+)-(\S+))/', trim($name), $match)) {

            $this->ast->vendor =  $match[2];
            $this->ast->name = $match[3];
        }

        else {

            $this->ast->name = (string) $name;
        }

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return $this
     */
    public function setVendor ($prefix) {

        if (is_null($prefix) || $prefix === '') {

            unset($this->ast->vendor);
        }

        else {

            $this->ast->vendor = (string) $prefix;
        }

        return $this;
    }
}