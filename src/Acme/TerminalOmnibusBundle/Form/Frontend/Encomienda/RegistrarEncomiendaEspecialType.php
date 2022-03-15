<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrarEncomiendaEspecialType extends AbstractType{

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
        $user = $options['user'];
        $builder->add('nombre', 'text', array(
             'label' => 'Nombre',
             'required' => true,
             'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('descripcion', 'textarea', array(
             'label' => 'Descripción',
             'required' => false,
             'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('permiteAutorizacionCortesia', 'checkbox', array(
             'label' => 'Permite Autorizacion Cortesia',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        $builder->add('permiteAutorizacionInterna', 'checkbox', array(
             'label' => 'Permite Autorizacion Interna',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        $builder->add('permitePorCobrar', 'checkbox', array(
             'label' => 'Permite Por Cobrar',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        $builder->add('permiteFactura', 'checkbox', array(
             'label' => 'Permite Factura',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        $builder->add('tarifaValor', 'number', array(
             'label' => 'Tarifa Valor',
             'required' => true,
             'precision' => 2,
             'attr' =>  array('class' => 'span12')
        ));
        
    }

    public function getName()
    {
        return 'registrar_encomienda_especial_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\EncomiendaEspecialModel',
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
