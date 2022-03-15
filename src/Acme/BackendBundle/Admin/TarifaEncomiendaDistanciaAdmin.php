<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TarifaEncomiendaDistanciaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tarifa_encomienda_distancia_admin';
    protected $baseRoutePattern = 'tarifaencomiendadistancia';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('estacionOrigen', null, array('label' => 'Estación Origen'))
        ->add('estacionDestino', null, array('label' => 'Estación Destino'))
        ->add('fechaEfectividad', null, array('format' => 'd/m/y H:i:s'))                
        ->add('tarifaValor', null, array('label' => 'Valor'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('estacionOrigen', null, array('label' => 'Estación Origen'))
        ->add('estacionDestino', null, array('label' => 'Estación Destino'))          
        ->add('tarifaValor', null, array('label' => 'Valor'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $anoInit = date('Y');
        $fechaEfectividad = $this->getSubject()->getFechaEfectividad();
        if($fechaEfectividad !== null){
            $anoInit = $fechaEfectividad->format('Y');
        }
        
        $mapper
            ->add('estacionOrigen', 'sonata_type_model' , array(
                    'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                    'label' => 'Estación Origen',
                    'property'=>'nombre',
                    'multiple'=>false,
                    'expanded'=>false,
                    'btn_add' => false,
                    'empty_value' => "",
                    'empty_data'  => null  
            )) 
            ->add('estacionDestino', 'sonata_type_model' , array(
                    'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                    'label' => 'Estación Destino',
                    'property'=>'nombre',
                    'multiple'=>false,
                    'expanded'=>false,
                    'btn_add' => false,
                    'empty_value' => "",
                    'empty_data'  => null  
            )) 
           ->add('fechaEfectividad', 'datetime', array(
               'label' => 'Fecha Efectividad',
               'years' => range($anoInit, date('Y') + 1)
           ))
           ->add('tarifaValor', null, array('label' => 'Valor'))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
     {
        $showMapper
            ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('estacionOrigen', null, array('label' => 'Estación Origen'))
            ->add('estacionDestino', null, array('label' => 'Estación Destino'))
            ->add('fechaEfectividad', null, array('label' => 'Fecha Efectividad'))
            ->add('tarifaValor', null, array('label' => 'Valor'))                
            ->add('fechaCreacion', null, array('label' => 'Fecha Creación'))                
            ->add('usuarioCreacion', null, array('label' => 'Usuario Creación'))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('edit');
        $collection->add('show');
    }
    
}

?>