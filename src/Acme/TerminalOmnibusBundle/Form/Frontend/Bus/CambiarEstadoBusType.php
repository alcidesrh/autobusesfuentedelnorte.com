<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Bus;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CambiarEstadoBusType extends AbstractType{

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
        
       $bus = $builder->getData();
       $builder->add('codigo', 'hidden');
       
       $builder->add('estado', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:EstadoBus',
            'label' => 'Estado',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span10')
        ));
    }

    public function getName()
    {
        return 'cambiar_estado_bus_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Bus',
 	    'cascade_validation' => true,
    	));
    }
}

?>
