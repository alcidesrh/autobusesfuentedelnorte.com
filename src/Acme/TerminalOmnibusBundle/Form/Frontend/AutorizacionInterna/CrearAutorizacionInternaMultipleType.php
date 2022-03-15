<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionInterna;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CrearAutorizacionInternaMultipleType extends AbstractType{

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
        $builder->add('cantidad', 'integer', array(
             'label' => 'Cantidad',
             'required' => true,
             'attr' =>  array('class' => 'span6')
        ));
        
        $builder->add('motivo', 'textarea', array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span6')
        ));
    }

    public function getName()
    {
        return 'crear_autorizacion_interna_multiple_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\AutorizacionInternaMultiplesModel',
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
