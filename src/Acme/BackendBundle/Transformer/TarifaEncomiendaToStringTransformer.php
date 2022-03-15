<?php

namespace Acme\BackendBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomienda;

class TarifaEncomiendaToStringTransformer implements DataTransformerInterface{
    
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($issue)
    {
        if (null === $issue) {
            return "";
        }
        return $issue->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $tarifaEncomienda = $this->om
            ->getRepository('AcmeTerminalOmnibusBundle:TarifaEncomienda')
            ->findOneBy(array('id' => $id))
        ;

        if (null === $tarifaEncomienda) {
            throw new TransformationFailedException(sprintf(
                'La tarifa de encomienda con id: "%s" no existe!',
                $id
            ));
        }

        return $tarifaEncomienda;
    }
}
