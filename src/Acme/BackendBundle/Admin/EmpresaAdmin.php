<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class EmpresaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_empresa_admin';
    protected $baseRoutePattern = 'empresa';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nit')
        ->add('alias')
        ->add('nombre')
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('nit')
        ->add('alias')
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
        
        $entity = $this->getSubject();
        $options = array(
            'label' => 'Logo',
            'required' => false
        );
        if($editar === true && $entity->getLogo() !== null){
             $options['help'] = '<img src="data:image/jpg;base64,' . $entity->getLogo() . '" />';
        }
        
        $mapper
        ->with('Básicos' , array('tab' => true))
            ->add('alias')
            ->add('nombre')
            ->add('nombreComercial')
            ->add('direccion', 'textarea', array('label' => 'Dirección'))
            ->add('activo', null, array('required' => false))       
        ->end()->end();
        
        $mapper->with('Información Tributaria' , array('tab' => true))
            ->add('nit', null, array('label' => 'NIT'))
            ->add('denominacionSocial')
            ->add('representanteLegal')
            ->add('formaPagoISR', null, array('label' => 'Forma de Pago de ISR'))
         ->end()->end();
        
        $mapper->with('Control de Talonarios' , array('tab' => true))
            ->add('obligatorioControlTarjetas', null, array(
                'label' => 'Requerido',
                'required' => false
                ))
         ->end()->end();
        
        $mapper->with('Reporte de Información a Servidor Externo' , array('tab' => true))
            ->add('idExterno', null, array('label' => 'ID Empresa'))
            ->add('idUsuarioExterno', null, array('label' => 'ID Usuario'))
            ->add('idClienteExterno', null, array('label' => 'ID Cliente'))
            ->add('urlExterno', null, array('label' => 'Url Externa'))
            ->add('reportarBoletoFacturado', null, array('label' => 'Reportar Boleto'))
            ->add('idProductoBoletoExterno', null, array('label' => 'ID Boleto'))
            ->add('reportarEncomiendaFacturado', null, array('label' => 'Reportar Encomienda'))
            ->add('idProductoEncomiendaExterno', null, array('label' => 'ID Encomienda'))
         ->end()->end();
        
        $mapper->with('Contacto' , array('tab' => true))
            ->add('correos', 'collection', array(
                'label' => 'Correos',
                'type'   => 'email',
                'options'  => array(
                    'label' => 'Valor',
                    'required'  => false,
                    'attr'      => array('class' => 'email-box')
                ),
                'allow_add'    => true,
                'allow_delete'    => true,
            ))
            ->add('telefonos', 'collection', array(
                'label' => 'Teléfonos',
                'type'   => 'text',
                'options'  => array(
                    'label' => 'Valor',
                    'required'  => false,
                    'attr'      => array('class' => 'email-box')
                ),
                'allow_add'    => true,
                'allow_delete'    => true,
            ))
        ->end()->end();
        
        $mapper->with('Logo', array('tab' => true))
            ->add('file', 'file', $options)
        ->end()->end();
        
        $mapper->with('Otros Datos', array('tab' => true))
            ->add('color', null,  array('attr' =>  array('class' => 'colpick')))
        ->end()->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
