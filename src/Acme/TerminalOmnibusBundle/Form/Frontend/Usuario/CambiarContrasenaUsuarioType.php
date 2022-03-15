<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Usuario;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CambiarContrasenaUsuarioType extends AbstractType{
    
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

        $builder->add('contrasenaAnterior', 'password', array(
            'label' => 'Contraseña Actual',
            'max_length' => 25,
            'required' => true,
            'attr' => array('class' => 'password-field input-xlarge')
        ));
        
        $builder->add('contrasenaNueva', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'Las contraseñas no coinciden.',
            'required' => true,
            'first_options'  => array(
                'label' => 'Nueva Contraseña', 
                'max_length' => 25, 
                'required' => true,
                'attr' => array('class' => 'password-field input-xlarge')
            ),
            'second_options' => array(
                'label' => 'Repita la Nueva Contraseña', 
                'max_length' => 25, 
                'required' => true,
                'attr' => array('class' => 'password-field input-xlarge')
            ),
        ));
       
    }

    public function getName()
    {
        return 'cambiar_cntrasena_usuario_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\CambiarContrasenaUsuarioModel',
 	    'cascade_validation' => true,
    	));
    }
}

?>
