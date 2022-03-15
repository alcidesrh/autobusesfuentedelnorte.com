<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ItinerarioCiclicoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_itinerarios_ciclicos_admin';
    protected $baseRoutePattern = 'itinerariosciclicos';
    
   //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('ruta')
        ->add('empresa')
        ->add('diaSemana')
        ->add('horarioCiclico')
        ->add('tipoBus')
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('ruta')
        ->add('empresa')
        ->add('diaSemana')
        ->add('horarioCiclico')
        ->add('tipoBus')
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
        
//        $itinerario = $mapper->getAdmin()->getSubject();
        $rutasQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\Ruta')
        ->createQuery(
            'SELECT r
             FROM AcmeTerminalOmnibusBundle:Ruta r
             ORDER BY r.codigo ASC, r.nombre ASC'
        );
        $diaSemanaQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\DiaSemana')
         ->createQuery(
            'SELECT ds
             FROM AcmeTerminalOmnibusBundle:DiaSemana ds
             ORDER BY ds.id ASC'
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
        
        $mapper
            ->add('ruta', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Ruta',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'read_only' => $editar,
                'disabled' => $editar,
                'query' => $rutasQuery
            ))
            ->add('empresa', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Empresa',
                'empty_value' => "",
                'empty_data'  => null,
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => false,
                'read_only' => $editar,
                'disabled' => $editar,
                'query' => $empresasQuery
            ))
            ->add('diaSemana', 'sonata_type_model' , array(
                    'label' => 'Día',
                    'class'=>'AcmeTerminalOmnibusBundle:DiaSemana',
                    'property'=>'nombre',
                    'multiple'=>false,
                    'expanded'=>false,
                    'btn_add' => false,
                    'read_only' => $editar,
                    'disabled' => $editar,
                    'query' => $diaSemanaQuery
             ))
             ->add('horarioCiclico', 'sonata_type_model' , array(
                    'label' => 'Hora',
                    'class'=>'AcmeTerminalOmnibusBundle:HorarioCiclico',
                    'multiple'=>false,
                    'expanded'=>false,
                    'read_only' => $editar,
                    'disabled' => $editar,
                    'btn_add' => false  
             ))
             ->add('tipoBus', 'sonata_type_model' , array(
                    'label' => 'Tipo de Bus',
                    'class'=>'AcmeTerminalOmnibusBundle:TipoBus',
                    'multiple'=>false,
                    'expanded'=>false,
                    'read_only' => $editar,
                    'disabled' => $editar,
                    'btn_add' => false,
                    'query' => $tiposBusQuery
             ))
             ->add('activo', null,  array('required' => false))
            ;      
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
