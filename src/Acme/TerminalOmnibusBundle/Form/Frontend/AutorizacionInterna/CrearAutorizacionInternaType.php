<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionInterna;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CrearAutorizacionInternaType extends AbstractType{

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
//       $entityManager = $options['em'];
        $builder->add('codigo', null, array(
             'label' => 'PIN',
             'required' => true,
             'read_only' => true,
             'attr' =>  array('class' => 'span3')
        ));
        
        $builder->add('motivo', null, array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'crear_autorizacion_interna_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\AutorizacionInterna',
 	    'cascade_validation' => true,
    	));
    }
}

?>
