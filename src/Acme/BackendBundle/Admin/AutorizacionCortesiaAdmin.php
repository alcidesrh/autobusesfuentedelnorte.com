<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;

class AutorizacionCortesiaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_autorizacion_cortesia_admin';
    protected $baseRoutePattern = 'autorizacioncortesia';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('servicioEstacion', null, array('label' => 'Servicio'))
        ->add('motivo', null, array('label' => 'Motivo'))
        ->add('codigo', null, array('label' => 'PIN'))
        ->add('notificarCliente', null, array('label' => 'Aviso Auto.'))
        ->add('fechaCreacion', null, array('label' => 'Fecha de Autorización'))
        ->add('usuarioCreacion', null, array('label' => 'Usuario que Autorizó'))
        ->add('fechaUtilizacion', null, array('label' => 'Fecha de Utilización'))
        ->add('usuarioUtilizacion', null, array('label' => 'Usuario que Utilizó'))
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('servicioEstacion', null, array('label' => 'Servicio'))
        ->add('motivo', null, array('label' => 'Motivo'))
        ->add('codigo', null, array('label' => 'PIN'))
        ->add('notificarCliente', null, array('label' => 'Aviso Auto.'))
        ->add('fechaCreacion', null, array('label' => 'Fecha de Autorización'))
        ->add('usuarioCreacion', null, array('label' => 'Usuario que Autorizó'))
        ->add('fechaUtilizacion', null, array('label' => 'Fecha de Utilización'))
        ->add('usuarioUtilizacion', null, array('label' => 'Usuario que Utilizó'))
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $isNew = true;
        if(strpos($this->request->getPathInfo(), "edit")){
            $isNew = false;
        }
        
        $autorizacion = $mapper->getAdmin()->getSubject();
        
        $mapper->with('General')
           ->add('listaAsientosDisponiblesBySalidaPath', 'hidden', array(
                'data' => $this->getConfigurationPool()->getContainer()->get("router")->generate("listaAsientosDisponiblesBySalida"),
                'virtual' => true,
                'attr' =>  array('class' => 'listaAsientosDisponiblesBySalidaPath')
            ))
            ->add('listarSalidasPath', 'hidden', array(
                'data' => $this->getConfigurationPool()->getContainer()->get("router")->generate("listarSalidas"),
                'virtual' => true,
                'attr' =>  array('class' => 'listarSalidasPath')
            ))
        ->end();
        
        $mapper
        ->with('General')
            ->add('servicioEstacion', 'sonata_type_model' , array(
                'label' => 'Servicio',
                'class'=>'AcmeTerminalOmnibusBundle:ServicioEstacion',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
            ))
            ->add('motivo', null, array('label' => 'Motivo'))
            ->add('codigo', null, array(
                'label' => 'PIN',
                'read_only' => true
             ))
            ->add('generatePin', 'hidden', array(
                 'data' => $this->generateUrl("generatePin"),
                 'virtual' => true,
                 'attr' =>  array('class' => 'generatePinHidden')
             ))
            ->add('notificarCliente', null, array('label' => 'Aviso Automático', 'required' => false))
            ->add('activo', null,  array('required' => false))
        ->end();
        
        $anoInit = date('Y');
        $restriccionFechaUso = $this->getSubject()->getRestriccionFechaUso();
        if($restriccionFechaUso !== null){
            $anoInit = $restriccionFechaUso->format('Y');
        }
        $mapper
        ->with('Restricciones')
             ->add('restriccionCliente', 'sonata_type_model', array(
                'required' => false,
                'label' => 'Cliente',
                'class'=>'AcmeTerminalOmnibusBundle:Cliente',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
             ))
             ->add('restriccionClaseAsiento', 'sonata_type_model', array(
                'required' => false,
                'label' => 'Clase de Asiento',
                'class'=>'AcmeTerminalOmnibusBundle:ClaseAsiento',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
             ))
            ->add('restriccionFechaUso', null, array(
                'label' => 'Fecha de Viaje',
                'required' => false,
                'label' => 'Fecha',
                'years' => range($anoInit, date('Y') + 1),
              ))
            ->add('restriccionEstacionOrigen', 'sonata_type_model', array(
                'required' => false,
                'label' => 'Estación Origen',
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
             ))
             ->add('restriccionEstacionDestino', 'sonata_type_model', array(
                'required' => false,
                'label' => 'Estación Destino',
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
             ))  
         ->end();
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $salidas = $em->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidas($autorizacion->getRestriccionFechaUso(), $autorizacion->getRestriccionEstacionOrigen(), $autorizacion->getRestriccionEstacionDestino());
        if(!$salidas){
            $salidas = array();
        }
        $asientos = $em->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientosDisponiblesBySalidaId($autorizacion->getRestriccionSalida(), $autorizacion->getRestriccionClaseAsiento(), $autorizacion->getRestriccionAsientoBus());
        if(!$asientos){
            $asientos = array();
        }
        $mapper
        ->with('Restricciones')
             ->add('restriccionSalida', 'entity' , array(
                'label' => 'Salida',
                'class'=>'AcmeTerminalOmnibusBundle:Salida',
                'choices' => $salidas,
                'multiple'=>false,
                'expanded'=>false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null,
                'attr' =>  array('class' => 'span4')
            ))
            ->add('restriccionAsientoBus', 'entity' , array(
                'label' => 'Asiento Bus',
                'class'=>'AcmeTerminalOmnibusBundle:AsientoBus',
                'choices' => $asientos,
                'multiple'=>false,
                'expanded'=>false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null,
                'attr' =>  array('class' => 'span4')
            ))
        ->end();
        $formSalidas = function(FormInterface $form, $container, $autorizacion) {
            $uniqid = $container->get('request')->query->get('uniqid');
            $commad = $container->get('request')->request->get($uniqid);
            $restriccionFechaUso = null;
            if($commad !== null && array_key_exists('restriccionFechaUso', $commad) && array_key_exists('day', $commad['restriccionFechaUso']) &&
                 array_key_exists('month', $commad['restriccionFechaUso']) && array_key_exists('year', $commad['restriccionFechaUso']) ){
                 $restriccionFechaUso = \DateTime::createFromFormat('d-m-Y', $commad['restriccionFechaUso']['day'] . 
                         "-" . $commad['restriccionFechaUso']['month'] . 
                         "-" . $commad['restriccionFechaUso']['year']);
            }else{
                $restriccionFechaUso = $autorizacion->getRestriccionFechaUso();
            }
            $restriccionEstacionOrigen = null;
            if($commad !== null && array_key_exists('restriccionEstacionOrigen', $commad)){
                $restriccionEstacionOrigen = $commad['restriccionEstacionOrigen'];
            }else{
                $restriccionEstacionOrigen = $autorizacion->getRestriccionEstacionOrigen();
            }
            $restriccionEstacionDestino = null;
            if($commad !== null && array_key_exists('restriccionEstacionDestino', $commad)){
                $restriccionEstacionDestino = $commad['restriccionEstacionDestino'];
            }else{
                $restriccionEstacionDestino = $autorizacion->getRestriccionEstacionDestino();
            }
            
            $salidas = array();
            if($restriccionFechaUso !== null && $restriccionEstacionOrigen !== null && $restriccionEstacionDestino !== null){
                $em = $container->get('doctrine')->getManager();
                $salidas = $em->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidas($restriccionFechaUso, $restriccionEstacionOrigen, $restriccionEstacionDestino);
                if(!$salidas){
                    $salidas = array();
                }
            }
            
            $restriccionSalida = null;
            if($commad !== null && array_key_exists('restriccionSalida', $commad)){
                $restriccionSalida = $em->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($commad['restriccionSalida']);
            }else{
                $restriccionSalida = $autorizacion->getRestriccionSalida();
            }
            $form->add('restriccionSalida', 'entity' , array(
                'label' => 'Salida',
                'class'=>'AcmeTerminalOmnibusBundle:Salida',
                'choices' => $salidas,
                'multiple'=>false,
                'expanded'=>false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null,
                'data' => $restriccionSalida,
                'attr' =>  array('class' => 'span4')
            ));
         };     
        $container = $this->getConfigurationPool()->getContainer();
        $mapper->getFormBuilder()->get('restriccionSalida')->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use ($formSalidas, $container, $autorizacion) {
                $formSalidas($event->getForm()->getParent(), $container, $autorizacion);
            }
        );
        //$autorizacion es la original sin modificaciones
        $formAsientoBus = function(FormInterface $form, $container, $autorizacion) {
            $uniqid = $container->get('request')->query->get('uniqid');
            $commad = $container->get('request')->request->get($uniqid);
            $restriccionSalida = null;
            if($commad !== null && array_key_exists('restriccionSalida', $commad)){
                $restriccionSalida = $commad['restriccionSalida'];
            }else{
                $restriccionSalida = $autorizacion->getRestriccionSalida();
            }
            $restriccionClaseAsiento = null;
            if($commad !== null && array_key_exists('restriccionClaseAsiento', $commad)){
                $restriccionClaseAsiento = $commad['restriccionClaseAsiento'];
            }else{
                $restriccionClaseAsiento = $autorizacion->getRestriccionClaseAsiento();
            }
            $em = $container->get('doctrine')->getManager();
            $asientos = array();
            if($restriccionSalida !== null && $restriccionClaseAsiento !== null){
                $asientos = $em->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientosDisponiblesBySalidaId($restriccionSalida, $restriccionClaseAsiento, $autorizacion->getRestriccionAsientoBus());
                if(!$asientos){
                    $asientos = array();
                }
            }
            $restriccionAsientoBus = null;
            if($commad !== null && array_key_exists('restriccionAsientoBus', $commad)){
                $restriccionAsientoBus = $em->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find($commad['restriccionAsientoBus']);
                $boleto = $autorizacion->getBoleto();
                if($boleto === null){
                    $boleto = new Boleto();
                    $autorizacion->setBoleto($boleto);
                    $boleto->setFechaCreacion(new \DateTime());
                    $usuarioCreacion = $container->get('security.context')->getToken()->getUser();
                    $boleto->setUsuarioCreacion($usuarioCreacion);
                    $boleto->setEstacionCreacion($usuarioCreacion->getEstacion());
                }else{
                    $boleto->setFechaActualizacion(new \DateTime());
                    $usuarioCreacion = $container->get('security.context')->getToken()->getUser();
                    $boleto->setUsuarioActualizacion($usuarioCreacion);
                }
                $boleto->setAsientoBus($restriccionAsientoBus);
                $boleto->setSalida($em->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($restriccionSalida));
                $boleto->setEstado($em->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::EMITIDO));
                $restriccionCliente = null;
                if($commad !== null && array_key_exists('restriccionCliente', $commad)){
                    $restriccionCliente = $commad['restriccionCliente'];
                }
                $cliente = $em->getRepository('AcmeTerminalOmnibusBundle:Cliente')->find($restriccionCliente);
                $boleto->setClienteDocumento($cliente);
                $boleto->setClienteBoleto($cliente);
                $restriccionEstacionOrigen = null;
                if($commad !== null && array_key_exists('restriccionEstacionOrigen', $commad)){
                    $restriccionEstacionOrigen = $commad['restriccionEstacionOrigen'];
                }
                $boleto->setEstacionOrigen($em->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($restriccionEstacionOrigen));
                $restriccionEstacionDestino = null;
                if($commad !== null && array_key_exists('restriccionEstacionDestino', $commad)){
                    $restriccionEstacionDestino = $commad['restriccionEstacionDestino'];
                }
                $boleto->setEstacionDestino($em->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($restriccionEstacionDestino));
                $boleto->setTipoDocumento($em->getRepository('AcmeTerminalOmnibusBundle:TipoDocumentoBoleto')->find(TipoDocumentoBoleto::AUTORIZACION_CORTESIA));
                $boleto->setAutorizacionCortesia($autorizacion);                
            }else{
                /*  Esto lo quite pq cuando cree un boleto de cortesia por el frondend y luego actualice la autorizacion, me pone el boleto en cancelado
                 * cuando no debe ser.
                */
//                $restriccionAsientoBus = $autorizacion->getRestriccionAsientoBus();
//                $boleto = $autorizacion->getBoleto();
//                if($boleto !== null){
//                    $boleto->setEstado($em->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CANCELADO));
//                    $boleto->setFechaActualizacion(new \DateTime());
//                    $usuarioCreacion = $container->get('security.context')->getToken()->getUser();
//                    $boleto->setUsuarioActualizacion($usuarioCreacion);
//                }
            }
            $form->add('restriccionAsientoBus', 'entity' , array(
                'label' => 'Asiento Bus',
                'class'=>'AcmeTerminalOmnibusBundle:AsientoBus',
                'choices' => $asientos,
                'multiple'=>false,
                'expanded'=>false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null,
                'data' => $restriccionAsientoBus,
                'attr' =>  array('class' => 'span4')
            ));
         };     
        $mapper->getFormBuilder()->get('restriccionAsientoBus')->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use ($formAsientoBus, $container, $autorizacion) {
            $formAsientoBus($event->getForm()->getParent(), $container, $autorizacion);
        });
        
        if(!$isNew && $autorizacion->getBoleto() !== null){
            $mapper
            ->with('Restricciones')
                 ->add('idBoleto', 'text', array(
                    'label' => 'Identificador del Boleto',
                    'virtual' => true,
                    'required' => false,
                    'read_only' => true,
                    'disabled' => true,
                    'attr' =>  array('class' => 'idBoleto span5'),
                    'data' => ($autorizacion->getBoleto() === null || $autorizacion->getBoleto()->getId() === null ) ? '' : $autorizacion->getBoleto()->getId()
                 ))
            ->end();
        }
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('generatePin');
        $collection->remove('delete');
    }
    
}

?>
