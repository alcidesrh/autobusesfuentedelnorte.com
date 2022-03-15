<?php

namespace Acme\BackendBundle\Exceptions;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class IpNotValidException extends AccountStatusException{
    
    
    public function getMessageKey()
    {
        return 'Usted no puede acceder desde esa dirección ip.';
    }
}
