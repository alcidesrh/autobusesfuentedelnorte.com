<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ImpresoraAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_impresora_admin';
    protected $baseRoutePattern = 'impresora';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nombre', null, array('label' => 'Nombre'))
        ->add('estacion', null, array('label' => 'Estación'))  
        ->add('path', null, array('label' => 'Path'))          
        ->add('activo', null, array('label' => 'Activo'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('nombre', null, array('label' => 'Nombre'))
        ->add('estacion', null, array('label' => 'Estación'))  
        ->add('path', null, array('label' => 'Path'))
        ->add('activo', null, array('label' => 'Activo'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->add('nombre', null, array('label' => 'Nombre'))
        ->add('estacion', 'sonata_type_model' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Estacion',
            'multiple'=>false,
            'expanded'=>false,
            'btn_add' => false,
            'required' => false,
            'empty_value' => "",
            'empty_data'  => null
        ))
        ->add('tipoImpresora', 'sonata_type_model' , array(
            'class'=>'AcmeTerminalOmnibusBundle:TipoImpresora',
            'multiple'=>false,
            'expanded'=>false,
            'btn_add' => false,
            'required' => false,
            'empty_value' => "",
            'empty_data'  => null
        ))
        ->add('path', null, array('label' => 'Path'))
        ->add('idTamanoPagina', null, array('label' => 'Id tamaño de página'))
        ->add('autoPrint', null,  array('required' => false))
        ->add('espacioLetras', null,  array('required' => false))
        ->add('activo', null,  array('required' => false))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
