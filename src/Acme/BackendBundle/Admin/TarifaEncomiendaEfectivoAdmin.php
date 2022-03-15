<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TarifaEncomiendaEfectivoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tarifa_encomienda_efectivo_admin';
    protected $baseRoutePattern = 'tarifaencomiendaefectivo';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('importeMinimo', null, array('label' => 'Mínimo'))
        ->add('importeMaximo', null, array('label' => 'Máximo'))
        ->add('fechaEfectividad', null, array('label' => 'Fecha Efectividad'))
        ->add('tarifaPorcentual', null, array('label' => 'Porcentual'))
        ->add('tarifaValor', null, array('label' => 'Valor'))                
        ->add('tarifaPorcentualValorMinimo', null, array('label' => 'Tarifa Mínima'))
        ->add('tarifaPorcentualValorMaximo', null, array('label' => 'Tarifa Máxima')) 
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('importeMinimo', null, array('label' => 'Mínimo'))
        ->add('importeMaximo', null, array('label' => 'Máximo'))
        ->add('tarifaPorcentual', null, array('label' => 'Porcentual'))
        ->add('tarifaValor', null, array('label' => 'Valor'))                
        ->add('tarifaPorcentualValorMinimo', null, array('label' => 'Tarifa Mínima'))
        ->add('tarifaPorcentualValorMaximo', null, array('label' => 'Tarifa Máxima')) 
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
           ->add('importeMinimo', null, array(
               'label' => 'Mínimo',
               'required' => false
             ))
           ->add('importeMaximo', null, array(
               'label' => 'Máximo',
               'required' => false
             ))
           ->add('fechaEfectividad', 'datetime', array(
               'label' => 'Fecha Efectividad',
               'years' => range($anoInit, date('Y') + 1)
           ))
           ->add('tarifaPorcentual', null, array(
                'label' => 'Porcentual',
                'required' => false
               ))
           ->add('tarifaValor', null, array('label' => 'Valor'))                
           ->add('tarifaPorcentualValorMinimo', null, array('label' => 'Tarifa Mínima'))
           ->add('tarifaPorcentualValorMaximo', null, array('label' => 'Tarifa Máxima')) 
        ;       
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
     {
        $showMapper
            ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('importeMinimo', null, array('label' => 'Mínimo'))
            ->add('importeMaximo', null, array('label' => 'Máximo'))
            ->add('fechaEfectividad', null, array('label' => 'Fecha Efectividad'))
            ->add('tarifaPorcentual', null, array('label' => 'Porcentual'))
            ->add('tarifaValor', null, array('label' => 'Valor'))                
            ->add('tarifaPorcentualValorMinimo', null, array('label' => 'Tarifa Mínima'))
            ->add('tarifaPorcentualValorMaximo', null, array('label' => 'Tarifa Máxima')) 
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