<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ConexionesAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_conexion_admin';
    protected $baseRoutePattern = 'conexion';
    protected $formOptions = array('cascade_validation' => true);
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('codigo', null, array('label' => 'Código'))
        ->add('nombre')
        ->add('horario')
        ->add('activo')
        ;
    }
    
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('codigo', null, array('label' => 'Código'))
        ->add('nombre')
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $editar = false;
        if(strpos($this->request->getPathInfo(), "edit")){
              $editar = true;
        }
        
        $mapper
        ->with('General')
            ->add('codigo', null, array(
                    'label' => 'Código',
                    'read_only' => $editar
                ))
            ->add('nombre')
            ->add('horario')
            ->add('precio')
            ->add('descripcion', 'textarea', array('label' => 'Descripción'))
            ->add('activo', null,  array('required' => false))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
