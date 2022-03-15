<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionInterna;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CancelarAutorizacionInternaType extends AbstractType{

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
        
       $autorizacionInterna = $builder->getData();
       $builder->add('id', 'hidden');

    }

    public function getName()
    {
        return 'cancelar_autorizacion_interna_command';
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
