<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Boleto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class RegistrarAutorizacionBoletoType extends AbstractType{

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
        
        $idBoleto = $options['idBoleto'];
        $builder->add('idBoleto', 'hidden', array(
            'required' => true,
            'virtual' => true,
            'data' =>  $idBoleto
        ));
        
        $builder->add('tipo', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoAutorizacionOperacion',
            'label' => 'Tipo de autorización',
            'property' => 'info',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder('e')
                            ->where('e.activo=1');
                return $query;
            }
        ));
       
        $builder->add('motivo', 'textarea', array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span12')
        ));
        
    }

    public function getName()
    {
        return 'registrar_autorizacion_boleto_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'idBoleto'
        ));
    }
}

?>
