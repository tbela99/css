<?php 

namespace TBela\CSS\Element;

use Exception;
use TBela\CSS\Value;

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
     * get node name
     * @param string $name
     * @return \TBela\CSS\Element
     */
    public function setName (string $name) {

        $name = trim($name);
        if (preg_match('/^(-([a-zA-Z]+)-(\S+))/', $name, $match)) {

            $this->ast->vendor =  $match[2];
            $this->ast->name = Value::escape($match[3]);
        }

        else {

            $this->ast->name = Value::escape($name);
        }

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return $this
     */
    public function setVendor ($prefix) {

        if (is_null($prefix) || (string) $prefix === '') {

            echo (new Exception())."\n\n";
            unset($this->ast->vendor);
        }

        else {

            $this->ast->vendor = $prefix;
        }

        return $this;
    }

    /**
     * set vendor prefix
     * @return string
     */
    public function getVendor() : string {

        if (isset($this->ast->vendor)) {

            return $this->ast->vendor;
        }

        return '';
    }
}