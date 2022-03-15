<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\MovilWeb;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class EmbarcarType extends AbstractType{

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
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
        $salidas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasParaMovil($user);
        $builder->add('salida', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Salida',
            'property' => 'info1',
            'choices' => $salidas,
            'label' => 'Salida', 
            'virtual' => true,
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
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
        return 'embarcar_command';
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
