<?php

namespace Acme\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TipoBusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder ->add('alias');
        $builder ->add('descripcion');
        $builder ->add('clase');
        $builder ->add('totalAsientos', null, array('read_only' => true));
        $builder ->add('listaServicios', null, array('required' => false));
        $builder ->add('activo', null, array('required' => false));
        $builder ->add('listaAsientoHidden', 'hidden');
        $builder ->add('listaSenalHidden', 'hidden');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\TipoBus',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'acme_backendbundle_tipobus_type';
    }
}
