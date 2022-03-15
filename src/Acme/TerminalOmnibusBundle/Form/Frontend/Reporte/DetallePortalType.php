<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class DetallePortalType extends AbstractType{

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
//        $user = $options["user"];
                
        $builder->add('rangoFecha', 'text',  array(
            'label' => 'Rango Fecha',
            'required' => false,
            'read_only' => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8')
        ));
        
        
        $builder->add('portal', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Portal WEB',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er){
                $items = array(
                    Estacion::ESTACION_PORTAL_INTERNET_MITOCHA,
                    Estacion::ESTACION_PORTAL_INTERNET_PIONERA
                );
                $query = $er->createQueryBuilder('e');
                $query->andWhere("e.id IN (".  implode(",", $items).")");
                return $query;
            }
        ));
        
        $builder->add('empresa', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Venta de Empresa',
            'property' => 'alias',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'read_only'  => false,
            'required'  => false,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er){
                $query = $er->createQueryBuilder('e');
                $query->andWhere('e.activo=1');
                return $query;
            }
        ));
    }

    public function getName()
    {
        return 'detallePortal';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\ReporteModel',
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
