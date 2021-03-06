<?php

namespace Acme\BackendBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class EditUserType extends AbstractType
{
    protected $dataClass = 'Acme\BackendBundle\Entity\User';
    protected $doctrine = null;
     
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
         parent::buildForm($builder, $options);
         
         $builder
            ->add('names', null, array('label' => 'Nombres'))
            ->add('surnames', null, array('label' => 'Apellidos'))
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('phone', null, array('label' => 'Teléfono'))
            ->add('intentosFallidos', null, array('label' => 'Intentos Fallidos'))
            ->add('accessAppWeb', null, array('label' => 'Access App Web', 'required' => false))
            ->add('accessAppMovil', null, array('label' => 'Access App Movil', 'required' => false))
            ->add('enabled', 'checkbox', array('label' => 'Activo', 'required' => false))
            ->add('locked', 'checkbox', array('label' => 'Bloqueado', 'required' => false))
            ->add('lastLogin', 'datetime', array(
                'label' => 'Último Acceso',
                'required'    => false,
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))
            ->add('expired', 'checkbox', array('label' => 'Expiro el acceso', 'required' => false))
            ->add('expiresAt', 'datetime', array(
                'label' => 'Fecha Expiración de Acceso',
                'required'    => false,
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))
            ->add('credentialsExpired', 'checkbox', array('label' => 'Expiro la contraseña', 'required' => false))
            ->add('credentialsExpireAt', 'datetime', array(
                'label' => 'Fecha Expiración de Contraseña',
                'required'    => false,
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))     
                 
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
        return 'acme_backendbundle_edit_user_type';
    }
}
