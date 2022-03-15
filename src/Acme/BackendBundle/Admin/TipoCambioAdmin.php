<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TipoCambioAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tipo_cambio_admin';
    protected $baseRoutePattern = 'tipocambio';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('fecha', null, array(
            'label' => 'Fecha',
            'format' => 'd/m/Y'
        ))
        ->add('moneda', null, array('label' => 'Moneda'))
        ->add('tipoTipoCambio', null, array('label' => 'Tipo de TipoCambio'))
        ->add('tasa', null, array('label' => 'Tasa'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('fecha', null, array('label' => 'Fecha'))
        ->add('moneda', null, array('label' => 'Moneda'))
        ->add('tipoTipoCambio', null, array('label' => 'Tipo de TipoCambio'))
        ->add('tasa', null, array('label' => 'Tasa'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $isNew = true;
        if(strpos($this->request->getPathInfo(), "edit")){
            $isNew = false;
        } 
        
        $anoInit = date('Y');
        $fecha = $this->getSubject()->getFecha();
        if($fecha !== null){
            $anoInit = $fecha->format('Y');
        }
        
        $mapper
        ->with('General')
            ->add('moneda', 'sonata_type_model' , array(
                'label' => 'Moneda',
                'class'=>'AcmeTerminalOmnibusBundle:Moneda',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'read_only' => !$isNew,
                'disabled' => !$isNew,
            ))
            ->add('tipoTipoCambio', 'sonata_type_model' , array(
                'label' => 'Tipo de TipoCambio',
                'class'=>'AcmeTerminalOmnibusBundle:TipoTipoCambio',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'read_only' => !$isNew,
                'disabled' => !$isNew,
            ))
             ->add('fecha', null, array(
               'label' => 'Fecha',
               'years' => range($anoInit - 1, date('Y') + 1),
               'read_only' => !$isNew,
               'disabled' => !$isNew,
            ))    
            ->add('tasa', null , array(
                'label' => 'Tasa',
                'required' => true,
                'attr' =>  array('class' => 'span5'),
            ))
        ->end();
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
     {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('fecha', null, array('label' => 'Fecha'))
            ->add('moneda', null, array('label' => 'Moneda'))
            ->add('tipoTipoCambio', null, array('label' => 'Tipo de TipoCambio'))
            ->add('tasa', null, array('label' => 'Tasa'))
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
