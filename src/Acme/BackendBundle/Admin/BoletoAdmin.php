<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class BoletoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_boleto_admin';
    protected $baseRoutePattern = 'boleto';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('salida', null, array('label' => 'Salida'))
        ->add('asientoBus', null, array('label' => 'Asiento'))
        ->add('clienteDocumento', null, array('label' => 'Cliente Documento'))
        ->add('clienteBoleto', null, array('label' => 'Cliente Boleto'))
        ->add('estado', null, array( 'label' => 'Estado' ))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('salida', null, array('label' => 'Salida'))
            ->add('asientoBus', null, array('label' => 'Asiento'))        
            ->add('revendidoEnEstacion', null, array('label' => 'Revendido Estación'))
            ->add('revendidoEnCamino', null, array('label' => 'Revendido Camino'))
            ->add('reasignado', null, array('label' => 'Reasignado'))
            ->add('clienteDocumento', null, array('label' => 'Cliente Documento'))
            ->add('clienteBoleto', null, array('label' => 'Cliente Boleto'))
            ->add('tipoPago', null, array('label' => 'Tipo Pago'))
            ->add('estacionOrigen', null, array('label' => 'Estación Origen'))        
            ->add('estacionDestino', null, array('label' => 'Estación Destino'))
            ->add('observacionDestinoIntermedio', null, array('label' => 'Observacion Destino'))
            ->add('utilizarDesdeEstacionOrigenSalida', null, array('label' => 'Utilizar desde estación origen salida'))    
            ->add('tipoDocumento', null, array('label' => 'Tipo Documento'))
            ->add('tarifa', null, array('label' => 'Tarifa'))
            ->add('precioCalculadoMonedaBase', null, array('label' => 'Precio Calculado Moneda Base'))
            ->add('moneda', null, array('label' => 'Moneda'))
            ->add('tipoCambio', null, array('label' => 'Tipo Cambio'))
            ->add('precioCalculado', null, array('label' => 'Precio'))
            ->add('facturaGenerada', null, array('label' => 'Factura Generada', 'route' => array('name' => 'show')))
            ->add('estado', null, array('label' => 'Estado'))
            ->add('autorizacionCortesia', null, array('label' => 'Autorización Cortesía'))
            ->add('observacion', null, array('label' => 'Observación'))
            ->add('estacionCreacion', null, array('label' => 'Estación Creación'))
            ->add('fechaCreacion', null, array('label' => 'Fecha Creación'))
            ->add('usuarioCreacion', null, array('label' => 'Usuario Creación'))
            ->add('fechaActualizacion', null, array('label' => 'Fecha Actualizaión'))
            ->add('usuarioActualizacion', null, array('label' => 'Usuario Actualizaión'))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('listaAsientosBySalida');
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
        $collection->add('show');
    }
    
}

?>
