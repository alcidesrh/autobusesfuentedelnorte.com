<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TiempoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tiempo_admin';
    protected $baseRoutePattern = 'tiempo';
    protected $formOptions = array('cascade_validation' => true);
        
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('ruta', null, array())
        ->add('estacionDestino', null, array())
        ->add('claseBus', null, array())                
        ->add('minutos')         
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('ruta')
        ->add('estacionDestino', null, array())
        ->add('claseBus')      
        ->add('minutos')          
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {     
         $mapper->add('ruta', 'sonata_type_model' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Ruta',
            'multiple'=>false,
            'expanded'=>false,
            'btn_add' => false
         ));
        
         $erEstacion = $this->modelManager->getEntityManager("AcmeTerminalOmnibusBundle:Estacion");
         $query = $erEstacion->createQueryBuilder('e')
                ->select('e')
                ->from('AcmeTerminalOmnibusBundle:Estacion', 'e')
                ->andwhere('e.destino=1')
                ->orderBy('e.nombre');
        
         $mapper->add('estacionDestino', 'sonata_type_model' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Estacion',
            'multiple'=>false,
            'expanded'=>false,
            'btn_add' => false,
            'query' => $query
         ));
         
         
         $mapper->add('claseBus', 'sonata_type_model' , array(
            'class'=>'AcmeTerminalOmnibusBundle:ClaseBus',
            'multiple'=>false,
            'expanded'=>false,
            'btn_add' => false
         ))
         ->add('minutos')
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
