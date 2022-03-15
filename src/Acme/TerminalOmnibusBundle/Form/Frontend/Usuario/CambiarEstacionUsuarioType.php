<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Usuario;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CambiarEstacionUsuarioType extends AbstractType{
    
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
         $estacionUsuario = $user->getEstacion();
         $builder->add('estacion', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación',
            'property' => 'aliasNombre',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'data' => $estacionUsuario,
            'attr' =>  array('class' => 'span12'),
            'choices' => $user->getEstacionesPermitidas(),
         ));
        
    }

    public function getName()
    {
        return 'cambiar_estacion_usuario_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\CambiarEstacionUsuarioModel',
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
