<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TarifaEncomiendaPaquetesVolumenAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tarifa_encomienda_paquetes_volumen_admin';
    protected $baseRoutePattern = 'tarifaencomiendapaquetesvolumen';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('volumenMinimo', null, array('label' => 'Volumen Mínimo'))
        ->add('volumenMaximo', null, array('label' => 'Volumen Máximo'))
        ->add('fechaEfectividad', null, array('label' => 'Fecha Efectividad'))
        ->add('tarifaPorcentual', null, array(
                'label' => 'Porcentual',
                'required' => false
            ))
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
        ->add('volumenMinimo', null, array('label' => 'Volumen Mínimo'))
        ->add('volumenMaximo', null, array('label' => 'Volumen Máximo'))
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
            ->add('volumenMinimo', null, array(
                'label' => 'Volumen Mínimo',
                'required' => false
            ))
            ->add('volumenMaximo', null, array(
                'label' => 'Volumen Máximo',
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
            ->add('volumenMinimo', null, array('label' => 'Volumen Mínimo'))
            ->add('volumenMaximo', null, array('label' => 'Volumen Máximo'))
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