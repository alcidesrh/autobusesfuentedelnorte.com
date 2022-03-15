<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ItinerarioEspecialAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_itinerario_especial_admin';
    protected $baseRoutePattern = 'itinerarioespecial';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID')) 
        ->add('fecha')
        ->add('estacionOrigen')
        ->add('ruta')
        ->add('empresa')
        ->add('tipoBus')           
        ->add('motivo')      
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
//        ->add('fecha')
        ->add('estacionOrigen')  
        ->add('ruta') 
        ->add('empresa')
        ->add('tipoBus')         
        ->add('motivo')      
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $rutasQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\Ruta')
        ->createQuery(
            'SELECT r
             FROM AcmeTerminalOmnibusBundle:Ruta r
             ORDER BY r.codigo ASC, r.nombre ASC'
        );
        $estacionesQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\Estacion')
        ->createQuery(
            'SELECT es
             FROM AcmeTerminalOmnibusBundle:Estacion es
             ORDER BY es.alias ASC, es.nombre ASC'
        );
        $tiposBusQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\TipoBus')
        ->createQuery(
            'SELECT tb
             FROM AcmeTerminalOmnibusBundle:TipoBus tb
             ORDER BY tb.alias ASC, tb.descripcion ASC'
        );
        $empresasQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\Empresa')
        ->createQuery(
            'SELECT em
             FROM AcmeTerminalOmnibusBundle:Empresa em
             ORDER BY em.nombre ASC'
        );
        
        $anoInit = date('Y');
        $fecha = $this->getSubject()->getFecha();
        if($fecha !== null){
            $anoInit = $fecha->format('Y');
        }
        $mapper
        ->add('fecha', 'datetime', array(
                 'years' => range($anoInit, date('Y') + 1)       
            ))
        ->add('ruta', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Ruta',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'query' => $rutasQuery
            ))
        ->add('empresa', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Empresa',
//                'empty_value' => "",
//                'empty_data'  => null,
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'query' => $empresasQuery
            ))
        ->add('estacionOrigen', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'query' => $estacionesQuery
            ))
        ->add('tipoBus', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:TipoBus',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'query' => $tiposBusQuery
            ))                
        ->add('motivo')
        ->add('activo', null,  array('required' => false))
        ;
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
