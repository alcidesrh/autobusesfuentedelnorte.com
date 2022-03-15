<?php

namespace Acme\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class RutaType extends AbstractType
{
    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $entity = $builder->getData();
        $edit = $options['edit'];
                
        $builder
                ->add('codigo', null, array(
                    'label' => 'Código',
                    'read_only' => $edit,
                    'attr' =>  array('class' => 'span6')
                ))
                ->add('nombre', null, array(
                    'label' => 'Nombre',
                    'attr' =>  array('class' => 'span6')
                ))
                ->add('kilometros', null, array(
                    'label' => 'Kilómetros',
                    'attr' =>  array('class' => 'span6')
                ))
                ->add('internacional', null,  array(
                    'label' => 'Internacional',
                    'required' => false
                ))
                ->add('descripcion', null, array(
                    'label' => 'Descripción',
                    'attr' =>  array('class' => 'span6')
                ))
                ->add('activo', null, array(
                    'required' => false
                ))
                ->add('obligatorioClienteDetalle', null,  array(
                    'label' => 'Cliente detallado',
                    'required' => false
                ))
                ->add('codigoFrontera', null,  array(
                    'label' => 'Código Frontera'
                ))
                ->add('estacionOrigen', 'entity' , array(
                    'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                    'label' => 'Estación de Origen',
                    'required' => true,
                    'multiple'  => false,
                    'expanded'  => false,
                    'empty_value' => "",
                    'empty_data'  => null,
                    'attr' =>  array('class' => 'span4'),
                    'query_builder' => function(EntityRepository $er){
                        $query = $er->createQueryBuilder('e')
                                    ->andwhere('e.destino=1')
                                    ->orderBy('e.nombre');       
                        return $query;
                    }
                ))    
                ->add('estacionDestino', 'entity' , array(
                    'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                    'label' => 'Estación de Destino',
                    'required' => true,
                    'multiple'  => false,
                    'expanded'  => false,
                    'empty_value' => "",
                    'empty_data'  => null,
                    'attr' =>  array('class' => 'span4'),
                    'query_builder' => function(EntityRepository $er){
                        $query = $er->createQueryBuilder('e')
                                    ->andwhere('e.destino=1')
                                    ->orderBy('e.nombre');       
                        return $query;
                    }
                ));
       
        $estacionesIntermediasJson = array();
        $estacionesIntermedias = $entity->getListaEstacionesIntermedia(true);
        $orden = 1;
        foreach ($estacionesIntermedias as $element) {
            $item = new \stdClass();
            $item->id = $element->getId();
            $item->posicion = $orden;
            $item->nombre = $element->getAliasNombre();
            $estacionesIntermediasJson[] =  $item;
            $orden++;
        }
        $builder ->add('listaEstacionesIntermediasHidden', 'hidden', array(
            'data' => json_encode($estacionesIntermediasJson),
            'virtual' => true
        )); 
        
        $estacionesDisponiblesJson = array();
        $estaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getAllDestinosEstacionesActivas();
        $estacionesDisponibles = array_diff($estaciones, $estacionesIntermedias);
        foreach ($estacionesDisponibles as $element) {
            $item = new \stdClass();
            $item->id = $element->getId();
            $item->nombre = $element->getAliasNombre();
            $estacionesDisponiblesJson[] =  $item;
        }
        $builder ->add('listaEstacionesDisponiblesHidden', 'hidden', array(
            'data' => json_encode($estacionesDisponiblesJson),
            'virtual' => true
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Ruta',
            'cascade_validation' => true,
        ))->setRequired(array(
            'edit',
        ));
    }

    public function getName()
    {
        return 'acme_backendbundle_ruta_type';
    }
}
