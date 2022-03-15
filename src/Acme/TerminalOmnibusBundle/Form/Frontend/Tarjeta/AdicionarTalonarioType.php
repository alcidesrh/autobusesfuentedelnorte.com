<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class AdicionarTalonarioType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        /*****************************************
         *          OTRAS PROPIEDADES
         * ***************************************
         *  'required' => true,
         *  'read_only' => true,
         *  
         */ 
       $entityManager = $options['em'];
       $salida = $options['salida'];
       $user = $options['user'];
       $tarjeta = $builder->getData();
       
       $builder->add('salida', 'hidden', array(
           'data' => $salida,
           'virtual' => true
       ));        
       $builder->add('inicial', 'integer' , array(
            'label' => 'Del',
            'required' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
       ));
        
       $builder->add('final', 'integer' , array(
            'label' => 'Al',
            'required' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
       ));
    }

    public function getName()
    {
        return 'talonario_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Talonario',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'salida',
            'user',
            'em',
        ))->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
