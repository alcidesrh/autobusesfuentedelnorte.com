<?php

namespace Acme\BackendBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class CreateUserType extends BaseType
{
    protected $dataClass = 'Acme\BackendBundle\Entity\User';
    protected $doctrine = null;
     
    public function __construct($doctrine)
    {
        parent::__construct($this->dataClass);
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
         $builder
            ->add('names', null, array('label' => 'Nombres'))
            ->add('surnames', null, array('label' => 'Apellidos'))
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('phone', null, array('label' => 'Teléfono'))
            ->add('accessAppWeb', null, array('label' => 'Access App Web', 'required' => false))
            ->add('accessAppMovil', null, array('label' => 'Access App Movil', 'required' => false))     
            ->add('enabled', null, array('label' => 'Activo', 'required' => false))
         ;
         
         $builder->add('estacion', 'entity', array(
            'label' => 'Estación',
            'class' => 'Acme\TerminalOmnibusBundle\Entity\Estacion',
            'multiple'  => false,
            'expanded'  => false,
            'required'    => false
         ));         
         
         $builder->add('estacionesPermitidas', 'entity', array(
            'label' => 'Estaciones Permitidas',
            'class' => 'Acme\TerminalOmnibusBundle\Entity\Estacion',
            'multiple' => true,
            'expanded' => false,
            'required' => false
         ));
         
         $builder->add('empresas', 'entity', array(
            'label' => 'Empresas',
            'class' => 'Acme\TerminalOmnibusBundle\Entity\Empresa',
            'multiple' => true,
            'expanded' => true,
            'required' => false
         ));
                  
         $repository = $this->doctrine->getRepository('AcmeBackendBundle:Rol'); 
         $listaRoles = $repository->findAll();
         $choices = array();
         foreach ($listaRoles as $rol){
             $nombre = $rol->getNombre();
             $choices[$nombre] = $nombre;
         }
   
         $builder->add('roles' , "choice",  array(
             'label' => 'Roles',
             'multiple'  => true,
             'expanded'  => true,
             'choices'   => $choices,
             'required'    => true
          ));
         
         $builder->add('ipRanges', 'collection', array(
            'label' => 'Rangos de Ip',
            'type'   => 'text',
            'prototype' => true,
            'prototype_name' => 'Ip',
            'allow_add'    => true,
            'allow_delete'    => true,
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
        return 'acme_backendbundle_create_user_type';
    }
}
