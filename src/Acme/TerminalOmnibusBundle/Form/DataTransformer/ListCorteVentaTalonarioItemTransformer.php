<?php

namespace Acme\TerminalOmnibusBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class ListCorteVentaTalonarioItemTransformer implements DataTransformerInterface{

    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($list)
    {
        $data = array();
        if (null !== $list) {
            foreach ($list as $item) {
                $data[] = array(
                    "id" => $item->getId(),
                    "numero" => $item->getNumero(),
                    "importe" => floatval($item->getImporte()),
                 );
            }
        }
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    public function reverseTransform($json)
    {
        $list = new \Doctrine\Common\Collections\ArrayCollection();
        
        if (!$json) { return $list; }
        
        $items = json_decode($json, true);
        
        foreach ($items as $item) {
            if(!isset($item["id"]) || !isset($item["importe"])){
                throw new TransformationFailedException(sprintf('El json de CorteVentaTalonarioItem es incorrecto'));
            }
            
            $id = $item["id"];
            $object = $this->om->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonarioItem')->findOneBy(array('id' => $id));
            if (null === $object) {
                throw new TransformationFailedException(sprintf(
                    'El CorteVentaTalonarioItem con identificador "%s" no existe!',
                    $id
                ));
            }
            
            if(trim($item["importe"]) === ""){
                $object->setImporte(0);
            }else{
                $object->setImporte(floatval($item["importe"]));
            }
            $list->add($object);
        }
        
        return $list;
    }
}
