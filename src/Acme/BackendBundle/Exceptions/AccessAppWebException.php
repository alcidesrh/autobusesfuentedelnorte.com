<?php

namespace Acme\BackendBundle\Exceptions;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccessAppWebException extends AccountStatusException{
    
    
    public function getMessageKey()
    {
        return 'Usted no está autorizado a acceder a la web.';
    }
}
