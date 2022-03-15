<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PilotoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_piloto_admin';
    protected $baseRoutePattern = 'piloto';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('codigo', null, array('label' => 'Código'))
        ->add('fullName', null, array('label' => 'Nombre Completo'))
        ->add('empresa', null, array('label' => 'Empresa'))         
        ->add('numeroLicencia', null, array('label' => 'Licencia'))
        ->add('fechaVencimientoLicencia', null, array(
            'label' => 'Vcto Licencia',
            'format' => 'd/m/Y'
        ))
        ->add('dpi', null, array('label' => 'DPI'))
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('codigo', null, array('label' => 'Código'))  
        ->add('numeroLicencia', null, array('label' => 'Licencia'))
        ->add('dpi', null, array('label' => 'DPI'))
        ->add('empresa', null, array('label' => 'Empresa'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->with('Personal')
            ->add('nacionalidad', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Nacionalidad',
                'label' => 'Nacionalidad',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null   
            ))
            ->add('dpi', null, array('label' => 'DPI'))
            ->add('nombre1', null, array('label' => 'Primer Nombre'))
            ->add('nombre2', null, array('label' => 'Segundo Nombre'))
            ->add('apellido1', null, array('label' => 'Primer Apellido'))
            ->add('apellido2', null, array('label' => 'Segundo Apellido'))
            ->add('fechaNacimiento', null, array(
                   'years' => range(date('Y') - 100, date('Y'))                
            ))
            ->add('sexo', null, array('label' => 'Sexo'))
            ->add('telefono', null, array('label' => 'Teléfono')) 
        ->end();
        
        $mapper
        ->with('Administrativo')
            ->add('empresa', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Empresa',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null   
            ))
            ->add('codigo', null, array('label' => 'Código empleado'))  
            ->add('seguroSocial')
            ->add('activo', null,  array('required' => false))
        ->end();   
        
        $anoInit = date('Y');
        $fechaVencimientoLicencia = $this->getSubject()->getFechaVencimientoLicencia();
        if($fechaVencimientoLicencia !== null){
            $anoInit = $fechaVencimientoLicencia->format('Y');
        }
        
        $mapper
        ->with('Licencia')     
            ->add('numeroLicencia', null, array('label' => 'Número de Licencia'))
            ->add('fechaVencimientoLicencia', null, array(
                    'years' => range($anoInit - 2, date('Y') + 20),
                    'label' => 'Fecha Vencimiento'
                ))
        ->end(); 
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
