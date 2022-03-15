<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class BusAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_bus_admin';
    protected $baseRoutePattern = 'bus';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('codigo', null, array('label' => 'Código'))
        ->add('empresa')        
        ->add('placa')
        ->add('tipo')     
        ->add('estado')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('codigo', null, array('label' => 'Código'))
        ->add('empresa')
        ->add('placa')
        ->add('tipo')
        ->add('estado')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        
        $editarCodigo = true;
        if(strpos($this->request->getPathInfo(), "edit")){
              $editarCodigo = false;
         }   
         
        $anoInit = date('Y') - 2;
        $fechaVencimientoTarjetaOperaciones = $this->getSubject()->getFechaVencimientoTarjetaOperaciones();
        if($fechaVencimientoTarjetaOperaciones !== null){
            $anoInit = $fechaVencimientoTarjetaOperaciones->format('Y') - 2;
        }
        $mapper
        ->with('General')
            ->add('codigo', null, array(
                    'label' => 'Código',
                    'read_only' => !$editarCodigo
                ))
            ->add('empresa', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Empresa',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null   
            ))
            ->add('placa')
            ->add('tipo', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:TipoBus',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null   
            ))
            ->add('marca', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:MarcaBus',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null  
            ))
            ->add('anoFabricacion', null, array('label' => 'Año de Fabricación'))
            ->add('estado', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:EstadoBus',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null
            )) 
            ->add('numeroSeguro', null, array('label' => 'Número de Seguro'))
            ->add('numeroTarjetaRodaje', null, array('label' => 'Número de Tarjeta de Rodaje'))
            ->add('numeroTarjetaOperaciones', null, array('label' => 'Número de Tarjeta de Operaciones'))
            ->add('fechaVencimientoTarjetaOperaciones', null, array(
                'years' => range($anoInit, date('Y') + 20),
                'label' => 'Fecha Vencimiento de Tarjeta de Operaciones'
            ))
            ->add('descripcion', null, array('label' => 'Descripción'))                
        ->end();
        
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        //Removing a single route
        $collection->remove('delete');
        
        //Only `list` and `edit` route will be active
        //$collection->clearExcept(array('list', 'edit'));
        //All routes are removed
        //$collection->clear(); 
        // prevent display of "Add new" when embedding this form
        //if($this->hasParentFieldDescription()) { 
        //$collection->remove('create');
        //}
    }
    
}

?>
