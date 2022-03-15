<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Caja;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PreCerrarCajaType extends AbstractType{

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
        
       $caja = $builder->getData();
       $builder->add('id', 'hidden');
       
       $importeTotal = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:OperacionCaja')->obtenerImporteTotal($caja);
               
       $builder->add('importeTotal', 'text',  array(
            'label' => 'Importe Total', 
            'read_only' => true,
            'virtual' => true,
            'required' => true,
            'data' => abs($importeTotal),
            'attr' =>  array('class' => 'span9'),
        ));
    }

    public function getName()
    {
        return 'precerrar_caja_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Caja',
 	    'cascade_validation' => true,
    	));
    }
}

?>
