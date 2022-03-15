<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ClienteAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_cliente_admin';
    protected $baseRoutePattern = 'cliente';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nit')
        ->add('dpi')
        ->add('nombre', null, array('label' => 'Nombre y Apellidos'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('nit')
        ->add('dpi')
        ->add('nombre', null, array('label' => 'Nombre y Apellidos'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $edit = false;
        if(strpos($this->request->getPathInfo(), "edit")){
              $edit = true;
         }  
         
        $mapper
        ->with('General')
            ->add('nit')
            ->add('dpi')
            ->add('nombre', null, array('label' => 'Nombre y Apellidos'))
            ->add('direccion', null, array('label' => 'Dirección'))
            ->add('telefono', null, array('label' => 'Teléfono'))
            ->add('correo')
        ->end();
        
        if($edit === true){
            $mapper
            ->with('General')
                ->add('usuarioCreacion', 'sonata_type_model' , array(
                    'label' => 'Usuario Creación',
                    'class'=>'Acme\BackendBundle\Entity\User',
                    'multiple'=>false,
                    'expanded'=>false,
                    'btn_add' => false,
                    'required' => true,
                    'empty_value' => "",
                    'empty_data'  => null,
                    'read_only' => true,
                    'disabled' => true,
                ))
                ->add('fechaCreacion', null, array(
                    'label' => 'Fecha Creación',
                    'read_only' => true,
                    'disabled' => true,
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy HH:mm:ss'
                ))
            ->end();
        }
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
