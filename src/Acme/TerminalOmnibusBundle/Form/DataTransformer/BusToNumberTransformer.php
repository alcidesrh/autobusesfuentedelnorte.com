<?php

namespace Acme\TerminalOmnibusBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class BusToNumberTransformer implements DataTransformerInterface{

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

        return $object->getCodigo();
    }

    public function reverseTransform($id)
    {
        if (!id) {
            return null;
        }

        $object = $this->om
            ->getRepository('AcmeTerminalOmnibusBundle:Bus')
            ->findOneBy(array('codigo' => $id))
        ;

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
                'El bus con identificador "%s" no existe!',
                $id
            ));
        }

        return $object;
    }
    
}
