<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class OperacionCajaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_operacion_caja_admin';
    protected $baseRoutePattern = 'operacioncaja';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('caja', null, array('label' => 'Caja'))
        ->add('importe', null, array('label' => 'Importe'))
        ->add('tipoOperacion', null, array('label' => 'Tipo de Operación'))        
        ->add('fecha', null, array('label' => 'Fecha'))
        ->add('empresa', null, array('label' => 'Empresa'))
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('caja', null, array('label' => 'Caja'))
        ->add('importe', null, array('label' => 'Importe'))
        ->add('tipoOperacion', null, array('label' => 'Tipo de Operación'))        
//        ->add('fecha', null, array('label' => 'Fecha'))   
        ->add('empresa', null, array('label' => 'Empresa'))        
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $isNew = true;
        if(strpos($this->request->getPathInfo(), "edit")){
            $isNew = false;
        } 
        
        $anoInit = date('Y');
        $fecha = $this->getSubject()->getFecha();
        if($fecha !== null){
            $anoInit = $fecha->format('Y');
        }
        
//        $signo = 1;
//        $importe = $this->getSubject()->getImporte();
//        if( $importe < 0){
//            $signo = -1;
//        }
//        $importe = abs($importe);
        
        $mapper
        ->with('General')
            ->add('caja', 'sonata_type_model' , array(
                'label' => 'Caja',
                'class'=>'AcmeTerminalOmnibusBundle:Caja',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'read_only' => !$isNew,
                'disabled' => !$isNew,
            ))
            ->add('importe', null , array(
                'label' => 'Importe',
                'required' => true,
                'attr' =>  array('class' => 'span5'),
//                'data' => $importe,
            ))
//            ->add('signo', 'choice', array(
//                'label' => 'Cr/Db',
//                'virtual' => true,
//                'required' => true,
//                'choices' => array('-1' => 'Cr', '1' => 'Db'),
//                'data' => $signo,
//            ))
            ->add('tipoOperacion', 'sonata_type_model' , array(
                'label' => 'Tipo de Operación',
                'class'=>'AcmeTerminalOmnibusBundle:TipoOperacionCaja',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null
            ))
            ->add('empresa', 'sonata_type_model' , array(
                'label' => 'Empresa',
                'class'=>'AcmeTerminalOmnibusBundle:Empresa',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null
            ))
            ->add('fecha', 'datetime', array(
               'label' => 'Fecha',
               'years' => range($anoInit - 10, date('Y') + 1),
               'read_only' => !$isNew,
               'disabled' => !$isNew,
           ))
           ->add('descripcion', null , array(
                'label' => 'Descripción',
                'required' => false,
            ))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
