<?php

namespace Acme\BackendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeBackendBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
