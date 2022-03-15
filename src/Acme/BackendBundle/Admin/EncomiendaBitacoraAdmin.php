<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class EncomiendaBitacoraAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_encomienda_bitacora_admin';
    protected $baseRoutePattern = 'encomiendabitacora';
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->add('fecha', 'datetime', array(
                'label' => 'Fecha',
                'required'    => false,
                'read_only' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))
            ->add('estacion', 'sonata_type_model' , array(
                'label' => 'Estación',
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null
            ))
           ->add('estado', 'sonata_type_model' , array(
                'label' => 'Estado',
                'class'=>'AcmeTerminalOmnibusBundle:EstadoEncomienda',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null
            ))
            ->add('usuario', 'sonata_type_model' , array(
                'label' => 'Usuario',
                'class'=>'Acme\BackendBundle\Entity\User',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null
            )) 
            ->add('salida', 'sonata_type_model' , array(
                'label' => 'Salida',
                'class'=>'AcmeTerminalOmnibusBundle:Salida',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null
            ))
            ->add('cliente', 'sonata_type_model' , array(
                'label' => 'Cliente',
                'class'=>'AcmeTerminalOmnibusBundle:Cliente',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => false,
                'empty_value' => "",
                'empty_data'  => null
            ))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('fecha', null, array('label' => 'Fecha'))
            ->add('estacion', null, array('label' => 'Estación'))     
            ->add('estado', null, array('label' => 'Estado'))
            ->add('usuario', null, array('label' => 'Usuario'))   
            ->add('salida', null, array('label' => 'Salida'))    
            ->add('cliente', null, array('label' => 'Cliente'))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->add('show');
    }
    
}

?>
