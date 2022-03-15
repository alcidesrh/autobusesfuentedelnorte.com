<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ImpresoraOperacionesAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_impresora_operaciones_admin';
    protected $baseRoutePattern = 'impresoraoperaciones';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('estacion', null, array('label' => 'Estación'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))    
        ->add('estacion', null, array('label' => 'Estación'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->with('General')
            ->add('estacion', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false  
            ))
            ->add('impresoraBoleto', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Impresora',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null
            )) 
            ->add('impresoraEncomienda', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Impresora',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null  
            ))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
