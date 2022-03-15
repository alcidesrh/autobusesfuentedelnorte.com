<?php

namespace Acme\TerminalOmnibusBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class TipoPagoToNumberTransformer implements DataTransformerInterface{

    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($object)
    {
        if (null === $object) {
            return "";
        }

        return $object->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $object = $this->om
            ->getRepository('AcmeTerminalOmnibusBundle:TipoPago')
            ->findOneBy(array('id' => $id))
        ;

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
                'El tipo de pago con identificador "%s" no existe!',
                $id
            ));
        }

        return $object;
    }
    
}
