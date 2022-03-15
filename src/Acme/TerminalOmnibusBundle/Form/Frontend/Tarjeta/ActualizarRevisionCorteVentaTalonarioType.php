<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ListCorteVentaTalonarioItemTransformer;
use Doctrine\ORM\EntityRepository;

class ActualizarRevisionCorteVentaTalonarioType extends AbstractType{

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
       $builder->add('id', 'hidden');
       
       $builder->add('listaItems', 'hidden');
       $builder->get('listaItems')->addModelTransformer(new ListCorteVentaTalonarioItemTransformer($entityManager));
    }

    public function getName()
    {
        return 'actualizar_revision_corte_venta_talonario_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\CorteVentaTalonario',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'em',
        ))
        ->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
