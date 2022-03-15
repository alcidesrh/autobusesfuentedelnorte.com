<?php

namespace Acme\BackendBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener {
   
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setFormat('xpi', 'application/x-xpinstall');
        $event->getRequest()->setFormat('lic', 'text/plain');
        $event->getRequest()->setFormat('mp4', 'video/mp4');
    }
}
