<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Configuracion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class TiempoType extends AbstractType{

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
//        $entityManager = $this->doctrine->getManager();
       
        $builder->add('ruta', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Ruta',
            'label' => 'Ruta',   
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span6'),
            'query_builder' => function(EntityRepository $er){
                $query = $er->createQueryBuilder('r')
                            ->where('r.activo=1');
                return $query;
            }
        ));
        
        $builder->add('claseBus', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:ClaseBus',
            'label' => 'Clase',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span6'),
            'query_builder' => function(EntityRepository $er){
                $query = $er->createQueryBuilder('u');
                $query->where('u.activo=1');
                return $query;
            }
        ));
        
        $builder->add('listaItems', 'hidden');
    }

    public function getName()
    {
        return 'configurar_tiempo_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\TiempoModel',
 	    'cascade_validation' => true,
    	));
    }
}

?>
