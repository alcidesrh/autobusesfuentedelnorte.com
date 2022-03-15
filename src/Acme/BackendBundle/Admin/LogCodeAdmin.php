<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class LogCodeAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_log_code_admin';
    protected $baseRoutePattern = 'logcode';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('codigo', null, array('label' => 'ID'))
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('codigo', null, array('label' => 'Código'))
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $editarCodigo = true;
        if(strpos($this->request->getPathInfo(), "edit")){
              $editarCodigo = false;
         }
         
        $mapper
        ->add('codigo', null, array(
            'label' => 'Código',
            'read_only' => !$editarCodigo
        ))
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
