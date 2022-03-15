<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class HorarioCiclicoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_horario_ciclico_admin';
    protected $baseRoutePattern = 'horariociclico';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID')) 
        ->add('hora')
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('hora')
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
//        $diaSemanaQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\DiaSemana')
//        ->createQuery(
//            'SELECT ds
//             FROM AcmeTerminalOmnibusBundle:DiaSemana ds
//             ORDER BY ds.id ASC'
//        );
            
        $mapper
//        ->add('diaSemana', 'sonata_type_model' , array(
//                'class'=>'AcmeTerminalOmnibusBundle:DiaSemana',
//                'property'=>'nombre',
//                'multiple'=>false,
//                'expanded'=>false,
//                'btn_add' => false,
//                'query' => $diaSemanaQuery
//            ))  
        ->add('hora', 'time')
        ->add('activo', null,  array('required' => false))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
