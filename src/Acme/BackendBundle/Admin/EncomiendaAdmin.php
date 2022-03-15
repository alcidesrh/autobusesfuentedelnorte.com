<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class EncomiendaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_encomienda_admin';
    protected $baseRoutePattern = 'encomienda';
    protected $formOptions = array(
        'cascade_validation' => true        
    );
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('cantidad', null, array('label' => 'Cantidad'))  
        ->add('descripcion', null, array('label' => 'Mercancia'))
        ->add('clienteRemitente', null, array('label' => 'Cliente Documento'))
        ->add('clienteDestinatario', null, array('label' => 'Cliente Boleto'))
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
            ->add('empresa', null, array('label' => 'Empresa'))
            ->add('cantidad', null, array('label' => 'Cantidad'))     
            ->add('tipoEncomienda', null, array('label' => 'Tipo Encomienda', 'route' => array('name' => 'show')))
            ->add('ruta', null, array('label' => 'Ruta'))   
            ->add('tipoEncomiendaEspecial', null, array('label' => 'Tipo Encomienda Especial'))   
            ->add('valorDeclarado', null, array('label' => 'Valor Declarado'))
            ->add('peso', null, array('label' => 'Peso'))   
            ->add('alto', null, array('label' => 'Alto'))
            ->add('ancho', null, array('label' => 'Ancho'))
            ->add('profundidad', null, array('label' => 'Profundidad'))
            ->add('volumen', null, array('label' => 'Volumen'))
            ->add('descripcion', null, array('label' => 'Descripción'))
            ->add('clienteRemitente', null, array('label' => 'Cliente Documento'))
            ->add('clienteDestinatario', null, array('label' => 'Cliente Boleto'))
            ->add('estacionOrigen', null, array('label' => 'Estación Origen'))    
            ->add('estacionDestino', null, array('label' => 'Estación Destino'))       
            ->add('tipoDocumento', null, array('label' => 'Tipo Documento'))   
            ->add('tarifa1', null, array('label' => 'Tarifa1')) 
            ->add('tarifa2', null, array('label' => 'Tarifa2')) 
            ->add('tarifaDistancia', null, array('label' => 'Tarifa Distancia')) 
            ->add('precioCalculadoMonedaBase', null, array('label' => 'Precio Calculado Moneda Base'))    
            ->add('moneda', null, array('label' => 'Moneda'))
            ->add('tipoCambio', null, array('label' => 'Tipo Cambio'))
            ->add('precioCalculado', null, array('label' => 'Precio'))
            ->add('tipoPago', null, array('label' => 'Tipo Pago'))   
            ->add('facturaGenerada', null, array('label' => 'Factura'))
            ->add('autorizacionCortesia', null, array('label' => 'Autorización Cortesía'))
            ->add('autorizacionInterna', null, array('label' => 'Autorización Interna'))
            ->add('codigo', null, array('label' => 'Código'))  
            ->add('observacion', null, array('label' => 'Observación'))
            ->add('boleto', null, array('label' => 'Boleto'))  
            ->add('estacionCreacion', null, array('label' => 'Estación Creación'))
            ->add('fechaCreacion', null, array('label' => 'Fecha Creación'))
            ->add('usuarioCreacion', null, array('label' => 'Usuario Creación'))
            ->add('eventos', null, array('label' => 'Estados', 'route' => array('name' => 'show')))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
        $collection->add('show');
    }
    
}

?>
