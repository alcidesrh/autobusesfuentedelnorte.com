<?php

namespace Acme\BackendBundle\Entity;

interface IJobSync{
    
    public function getDataArrayToSync();
    
    public function isValidToSync();
    
    public function getNivelSync();
    
    public function getTypeSync();
}

?>