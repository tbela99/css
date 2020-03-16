<?php 

namespace TBela\CSS;

use Exception;

trait ElementTrait  {

    /**
     * @param bool $getVendor
     * @return string
     * @throws Exception
     */
    public function getName($getVendor = true) {

        $vendor = $this->getVendor();

        if ($getVendor && $vendor !== '') {

            return '-'.$vendor.'-'.$this->ast->name;
        }

        return $this->ast->name;
    }

    /**
     * @return string
     */
    public function getVendor () {

        if (isset($this->ast->vendor)) {

            return $this->ast->vendor;
        }

        return '';
    }

    /**
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
     * @param null|string $prefix
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