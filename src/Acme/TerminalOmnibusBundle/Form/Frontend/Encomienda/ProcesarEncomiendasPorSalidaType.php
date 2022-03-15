<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ProcesarEncomiendasPorSalidaType extends AbstractType{

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
        
        $user = $options["user"];
//        $estacionUsuario = $user->getEstacion();
        $salidas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasParaMovilEncomiendas($user);
        $builder->add('salida', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Salida',
            'property' => 'info1',
            'choices' => $salidas,
            'label' => 'Salida', 
            'empty_value' => "",
            'empty_data'  => null,
            'virtual' => true,
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span12 salida'),
        ));
        
    }

    public function getName()
    {
        return 'procesar_encomiendas_salida_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\GenericModel',
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
