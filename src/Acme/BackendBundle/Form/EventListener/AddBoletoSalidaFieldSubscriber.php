<?php

namespace Acme\BackendBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sonata\AdminBundle\Form\FormMapper;

class AddBoletoSalidaFieldSubscriber implements EventSubscriberInterface{
    
    protected $mapper;
    protected $modelManager;
    
    function __construct(FormMapper $mapper, $modelManager) {
        $this->mapper = $mapper;
        $this->modelManager = $modelManager;
    }
    
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        var_dump($data);
        if($data !== null && array_key_exists('salida', $data)) {
            $this->refreshListaAsiento($data['salida']);
        }else{
            $this->refreshListaAsiento(null);
        }
    }
    
    private function refreshListaAsiento($idSalida){
        var_dump("refreshListaAsiento----init .... " . $idSalida);
        if($idSalida !== null){
            var_dump("x11");
             $asientoBusQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\AsientoBus')
            ->createQuery(
                  " SELECT a from Acme\TerminalOmnibusBundle\Entity\AsientoBus a "
                . " JOIN a.tipoBus tba, "
                . " Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " JOIN s.tipoBus tbs "
                . " WHERE "
                . " tba.id = tbs.id and "
                . " s.id = :idSalida "
                . " ORDER BY a.numero ASC "
            )->setParameter('idSalida', $idSalida);
          
            $this->mapper->with('General')
                ->add('asientoBus', 'sonata_type_model' , array(
                    'label' => 'Asiento',
                    'class'=>'AcmeTerminalOmnibusBundle:AsientoBus',
                    'property'=>'numero',
                    'multiple'=>false,
                    'expanded'=>false,
                    'btn_add' => false,
                    'query' => $asientoBusQuery
                 ))
            ->end(); 
         }  else {
             var_dump("x22");
            $this->mapper->with('General')
            ->add('asientoBus', 'sonata_type_model' , array(
                'label' => 'Asiento',
                'class'=>'AcmeTerminalOmnibusBundle:AsientoBus',
                'property'=>'numero',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
             ))
            ->end();
             
         }
             
                
//        $builder->add('users', 'entity', array(
//            'class' => 'AcmeHelloBundle:User',
//            'choices' => $group->getUsers(),
//        ));
        
//        $factory = $this->mapper->getFormBuilder()->getFormFactory();
//        $this->mapper->with('General')
//        ->add($factory->createNamed('entity', 'asientoBus' , null, array(
//            'class' => 'AcmeTerminalOmnibusBundle:AsientoBus',
//            'label'         => 'Asiento',
//            'query_builder' => function (EntityRepository $repository) use ($idSalida) {
//                if($idSalida === null || trim($idSalida) === ""){
//                    return array();
//                }
//                return $repository->getAsientosBySalidaId($idSalida);
//            }
//        )))
//        ->end();
        
        //         ->add('asientoBus', 'sonata_type_model' , array(
//                'label' => 'Asiento',
//                'class'=>'AcmeTerminalOmnibusBundle:AsientoBus',
//                'multiple'=>false,
//                'expanded'=>false,
//                'btn_add' => false,
//                'query' => function (EntityRepository $repository) use ($idSalida) {
//                    return $repository->getAsientosBySalidaId($idSalida);
//                }
//            ))
     }
}
