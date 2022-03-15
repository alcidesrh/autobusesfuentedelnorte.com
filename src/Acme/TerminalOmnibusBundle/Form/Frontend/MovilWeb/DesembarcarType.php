<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\MovilWeb;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DesembarcarType extends AbstractType{

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
        
        
        $builder->add('code', 'text',  array(
            'label' => 'Código Barra', 
            'virtual' => true,
            'read_only' => true,
            'disabled' => true,
            'attr' =>  array('class' => 'span12'),
        ));
        
//        $builder->add('resultado', 'textarea', array(
//             'label' => 'Resultado',
//             'virtual' => true,
//             'read_only' => true,
//             'disabled' => true,
//             'attr' =>  array('class' => 'span12 resultado', 'rows'=>'6')
//        ));
    }

    public function getName()
    {
        return 'desembarcar_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\ModelWebModel',
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
