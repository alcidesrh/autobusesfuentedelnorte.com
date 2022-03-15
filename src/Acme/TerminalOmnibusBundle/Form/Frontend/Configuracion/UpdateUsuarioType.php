<?php

namespace Acme\TerminalOmnibusBundle\Form\Frontend\Configuracion;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Acme\BackendBundle\Form\Model\CambiarContrasenaModel;

class UpdateUsuarioType extends AbstractType
{
    protected $dataClass = 'Acme\TerminalOmnibusBundle\Form\Model\UsuarioModel';
    protected $doctrine = null;
     
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        parent::buildForm($builder, $options);
         
        $builder
             ->add('user', 'entity',  array(
                'class' => 'AcmeBackendBundle:User',
                'label' => 'Usuario', 
                'required' => false,
                'multiple'  => false,
                'expanded'  => false,
                'empty_value' => "",
                'empty_data'  => null,
                'attr' =>  array('class' => 'span6'),
                'query_builder' => function(EntityRepository $er){
                    $query = $er->createQueryBuilder("u")
                            ->andWhere("u.enabled=1")
                            ->orderBy("u.username");
                    return $query;
                }
            ));
        
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'label' => ' ', 
            'required' => false,
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array(
                'label' => 'form.new_password',
                'attr' =>  array('class' => 'span6', 'placeholder' => 'Nueva contraseña')
            ),
            'second_options' => array(
                'label' => 'form.new_password_confirmation',
                'attr' =>  array('class' => 'span6', 'placeholder' => 'Repita la nueva contraseña')
            ),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        
        $builder
             ->add('estacion', 'entity',  array(
                'class' => 'AcmeTerminalOmnibusBundle:Estacion',
                'label' => 'Estación', 
                'required' => false,
                'multiple'  => false,
                'expanded'  => false,
                'empty_value' => "",
                'empty_data'  => null,
                'attr' =>  array('class' => 'span6')
            ));
        
        $builder->add('todasEstaciones', 'checkbox', array(
             'label' => 'Acceso a todas las estaciones',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        
        $builder->add('desbloquear', 'checkbox', array(
             'label' => 'Desbloquear',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'update_user';
    }
}
