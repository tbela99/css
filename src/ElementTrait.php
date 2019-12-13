<?php 

namespace TBela\CSS;

trait ElementTrait  {

    public function getName($getVendor = true) {

        $vendor = $this->getVendor();

        if ($getVendor && $vendor !== '') {

            return '-'.$vendor.'-'.$this->ast->name;
        }

        return $this->ast->name;
    }

    public function getVendor () {

        if (isset($this->ast->vendor)) {

            return $this->ast->vendor;
        }

        return '';
    }
}