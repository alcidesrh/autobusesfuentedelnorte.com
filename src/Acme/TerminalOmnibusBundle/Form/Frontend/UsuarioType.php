<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsuarioType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        /*****************************************
         *          OTRAS PROPIEDADES
         * ***************************************
         *  'required' => true,
         *  'read_only' => true,
         *  
         */ 

       $builder
            ->add('nombre', null,  array(
                    'label' => 'Nombre',             
                    'attr' =>  array('class' => 'nombre', 'accesskey' => 'n', 'tabindex' => '1', 'size' => '25')
                ))
            ->add('apellidos', null,  array(
                    'attr' =>  array('class' => 'apellidos', 'accesskey' => 'a', 'tabindex' => '2', 'size' => '25')
                ))
            ->add('fechaNacimiento', 'birthday', array(
                    'label' => 'Fecha nacimiento',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text', //single_text, text, choice                   
                    'attr' =>  array('class' => 'fechaNacimiento', 'accesskey' => 'f', 'tabindex' => '3', 'size' => '25')
                ))
            ->add('dni', null,  array(
                    'attr' =>  array('class' => 'dni', 'accesskey' => 'd', 'tabindex' => '4', 'size' => '25')
                ))
            ->add('direccion', null,  array(
                    'attr' =>  array('class' => 'direccion', 'accesskey' => 'r', 'tabindex' => '5', 'style' => 'width:100%')
                ))           
            ->add('permiteEmail', 'checkbox', array(
                    'label' => 'Permite envio email',
                    'required' => false,
                    'attr' =>  array('class' => 'permiteEmail', 'accesskey' => 'o', 'tabindex' => '8')
                ))
        ;
       
       if(null == $options['data']->getId()) //new item
       {
            $builder ->add('email', 'email', array(
                    'attr' =>  array('class' => 'email', 'accesskey' => 'e', 'tabindex' => '6', 'size' => '25')
                )) 
                   ->add('password', 'repeated', array(
                     'type' => 'password',
                     'invalid_message' => 'Las dos contraseñas deben coincidir',
                     'first_options' => array(
                         'label' => 'Contraseña',
                         'attr' =>  array('class' => 'password', 'accesskey' => 'c', 'tabindex' => '7', 'size' => '25'
                     )),
                     'second_options' => array(
                         'label' => 'Repita la contraseña',
                         'attr' =>  array('class' => 'password', 'accesskey' => 'r', 'tabindex' => '7', 'size' => '25'
                     )),
                     'required' => true                   
                ))
            ;
       }
       else
       {
            $builder ->add('email', 'email', array(
                    'attr' =>  array('class' => 'email', 'accesskey' => 'e', 'tabindex' => '6', 'size' => '25'),
                    'read_only' => true
                )) 
                   ->add('password', 'repeated', array(
                     'type' => 'password',
                     'invalid_message' => 'Las dos contraseñas deben coincidir',
                     'first_options' => array(
                         'label' => 'Contraseña',
                         'attr' =>  array('class' => 'password', 'accesskey' => 'c', 'tabindex' => '7', 'size' => '25'
                     )),
                     'second_options' => array(
                         'label' => 'Repita la contraseña',
                         'attr' =>  array('class' => 'password', 'accesskey' => 'r', 'tabindex' => '7', 'size' => '25'
                     )),
                     'required' => false 
                ))
            ;
       }
    }

    public function getName()
    {
        return 'usuario_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Usuario',
 	    'cascade_validation' => true,
    	));
    }
}

?>
