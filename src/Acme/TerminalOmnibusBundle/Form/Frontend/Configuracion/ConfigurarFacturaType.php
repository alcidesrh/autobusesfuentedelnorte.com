<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Configuracion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\FacturaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\EstacionToNumberTransformer;
use Doctrine\ORM\EntityRepository;

class ConfigurarFacturaType extends AbstractType{

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
        $entityManager = $this->doctrine->getManager();
        
        $builder->add('estacion', 'hidden');
        $builder->get('estacion')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        $builder->add('servicioEstacion', 'hidden');//Solo se maneja el valor como numero
        
        $builder->add('empresa', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span6')
        ));
        
        $builder->add('factura', 'text',  array(
            'label' => 'Factura',
            'required' => true,
            'attr' =>  array('class' => 'span6')
        ));
        $builder->get('factura')->addModelTransformer(new FacturaToNumberTransformer($entityManager));
    }

    public function getName()
    {
        return 'configurar_factura_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\ConfigurarFacturaModel',
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
