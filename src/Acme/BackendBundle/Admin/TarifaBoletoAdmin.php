<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TarifaBoletoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tarifa_boleto_admin';
    protected $baseRoutePattern = 'tarifaboleto';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('estacionOrigen', null, array('label' => 'Origen'))
        ->add('estacionDestino', null, array('label' => 'Destino'))
        ->add('claseBus')
        ->add('claseAsiento')
        ->add('horaInicialSalida')  
        ->add('horaFinalSalida')
        ->add('fechaEfectividad', null, array('format' => 'd/m/y H:i:s'))                
        ->add('tarifaValor', null, array('label' => 'Tarifa'))
        ->add('tarifaAdicionalAgencia', null, array('label' => 'Tarifa Agencia'))
        ->add('usuarioCreacion.username', null, array('label' => 'Usuario'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('estacionOrigen')
        ->add('estacionDestino')
        ->add('claseBus')
        ->add('claseAsiento')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        
        $estacionesQuery = $this->modelManager->getEntityManager('Acme\TerminalOmnibusBundle\Entity\Estacion')
        ->createQuery(
            'SELECT es
             FROM AcmeTerminalOmnibusBundle:Estacion es
             WHERE es.destino=1 and es.activo=1
             ORDER BY es.alias ASC, es.nombre ASC'
        );
        
        $mapper
        ->add('estacionOrigen', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'empty_value' => "",
                'empty_data'  => null,
                'query' => $estacionesQuery
        )) 
        ->add('estacionDestino', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'empty_value' => "",
                'empty_data'  => null,
                'query' => $estacionesQuery
        ))
        ->add('claseBus', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:ClaseBus',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'empty_value' => "",
                'empty_data'  => null  
        ))
        ->add('claseAsiento', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:ClaseAsiento',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'empty_value' => "",
                'empty_data'  => null  
        ));
        
        $anoInit = date('Y');
        $fechaEfectividad = $this->getSubject()->getFechaEfectividad();
        if($fechaEfectividad !== null){
            $anoInit = $fechaEfectividad->format('Y');
        }
        $mapper->add('fechaEfectividad', 'datetime', array(
                'years' => range($anoInit, date('Y') + 1)
        ))
        ->add('horaInicialSalida', 'time', array(
                'label' => 'Horario de salida a partir de:',
                'empty_value' => "",
                'empty_data'  => null,
                'required' => false
        ))  
        ->add('horaFinalSalida', 'time', array(
                'label' => 'Horario de salida hasta las:',
                'empty_value' => "",
                'empty_data'  => null,
                'required' => false
        ))
        ->add('tarifaValor')  
        ->add('tarifaAdicionalAgencia')
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
     {
        $showMapper
        ->with('General')
            ->add('id')
            ->add('estacionOrigen')
            ->add('estacionDestino')
            ->add('claseBus')
            ->add('claseAsiento')
            ->add('horaInicialSalida')  
            ->add('horaFinalSalida')   
            ->add('fechaEfectividad')                
            ->add('tarifaValor')
            ->add('tarifaAdicionalAgencia')
            ->add('fechaCreacion')                
            ->add('usuarioCreacion')
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('edit');
        $collection->add('show');
    }
    
}

?>