<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Bus;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ActualizarBusType extends AbstractType{

    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        /*****************************************
         *          OTRAS PROPIEDADES
         * ***************************************
         *  'required' => true,
         *  'read_only' => true,
         *  
         */ 
       
        $user = $options["user"];
        
        $builder->add('codigo', null, array(
            'label' => 'Código',
            'read_only' => true,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('empresa', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',
            'choices' => $user->getEmpresas(),
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null ,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('placa', null, array(
            'label' => 'Placa',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('tipo', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:TipoBus',
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('marca', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:MarcaBus',
            'property'=>'nombre',
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('anoFabricacion', null, array(
            'label' => 'Año de Fabricación',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('estado', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:EstadoBus',
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span8')
         ));
         $builder->add('numeroSeguro', null, array(
            'label' => 'Número de Seguro',
            'required' => false,
            'attr' =>  array('class' => 'span8')
         ));
         $builder->add('numeroTarjetaRodaje', null, array(
             'label' => 'Número de Tarjeta de Rodaje',
             'required' => false,
             'attr' =>  array('class' => 'span8')
         ));
         $builder->add('numeroTarjetaOperaciones', null, array(
             'label' => 'Número de Tarjeta de Operaciones',
             'required' => false,
             'attr' =>  array('class' => 'span8')
         ));
         $builder->add('fechaVencimientoTarjetaOperaciones', 'date',  array(
              'label' => 'Fecha Vencimiento de Tarjeta de Operaciones',
              'widget' => 'single_text',
              'format' => 'dd/MM/yyyy',
             'required' => false,
              'attr' =>  array('class' => 'span8')
         ));
         $builder->add('descripcion', null, array(
              'label' => 'Descripción',
              'required' => false,
              'attr' =>  array('class' => 'span12')
         ));
    }

    public function getName()
    {
        return 'actualizar_bus_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Bus',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
        ))
        ->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
        ));
    }
}

?>
