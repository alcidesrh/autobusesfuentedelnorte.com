<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionOperaciones;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class RechazarAutorizacionOperacionesType extends AbstractType{
    
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
        
        $builder->add('id', 'hidden');
         
        $builder->add('observacion', 'textarea', array(
             'label' => 'Observación',
             'required' => false,
             'max_length' => 100,
             'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'rechazar_autorizacion_operacion_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion',
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
