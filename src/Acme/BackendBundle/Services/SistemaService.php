<?php

namespace Acme\BackendBundle\Services;

use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\BackendBundle\Entity\Sistema;

class SistemaService {
   
    static public $VAR_FECHA_SISTEMA = 'FECHA_SISTEMA';
    static public $VAR_FECHA_SISTEMA_INC_AUTO = 'FECHA_SISTEMA_INC_AUTO';
    static public $MONEDA_BASE = 'MONEDA_BASE';
    
    protected $container;
    
    public function __construct($container) { 
        $this->container = $container;
   }
   
   public function getVariableSistema($codigo, $estacion = null){
       if($estacion === null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            if($user !== null){
                $estacion = $user->getEstacion();
                if($estacion === null){
                    throw new \RuntimeException("No se puede obtener la variable " . $codigo . " porque el usuario no tiene niguna estación definida.");
                }
            }else{
                throw new \RuntimeException("No se puede obtener la variable " . $codigo . " porque no se puedo obtener el usuario.");
            }   
       }
       return $this->container->get("doctrine")->getRepository('AcmeBackendBundle:Sistema')->getVariableSistema($codigo, $estacion);
   }
   
   public function sincronizarVariablesSistema(Estacion $estacion){
       
       $listado = array();
       if($estacion->getId() !== null && $estacion->getId() !== 0){
          $result = $this->container->get("doctrine")->getRepository('AcmeBackendBundle:Sistema')->listarVariablesPorEstacion($estacion);
          foreach ($result as $item) {
              $listado[$item->getCodigo()] = $item;
          }
       }
       
       $em = $this->container->get("doctrine")->getManager();
       if(!array_key_exists(SistemaService::$VAR_FECHA_SISTEMA, $listado)){
           $variable = new Sistema();
           $variable->setCodigo(SistemaService::$VAR_FECHA_SISTEMA);
           $variable->setEstacion($estacion);
           $date = new \DateTime();
           $variable->setValor($date->format("d-m-Y"));
           $em->persist($variable);
       }
       
       if(!array_key_exists(SistemaService::$VAR_FECHA_SISTEMA_INC_AUTO, $listado)){
           $variable = new Sistema();
           $variable->setCodigo(SistemaService::$VAR_FECHA_SISTEMA_INC_AUTO);
           $variable->setEstacion($estacion);
           $variable->setValor(1);
           $em->persist($variable);
       } 
       
       if(!array_key_exists(SistemaService::$MONEDA_BASE, $listado)){
           $variable = new Sistema();
           $variable->setCodigo(SistemaService::$MONEDA_BASE);
           $variable->setEstacion($estacion);
           $variable->setValor("GTQ");
           $em->persist($variable);
       } 
       
       $em->flush();
   }
   
}
