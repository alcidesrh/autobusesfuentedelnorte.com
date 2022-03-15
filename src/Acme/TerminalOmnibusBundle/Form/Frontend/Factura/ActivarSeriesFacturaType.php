<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Factura;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ActivarSeriesFacturaType extends AbstractType{

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
//       $entityManager = $options['em'];
//        $user = $options["user"];
        
        $builder->add('id', 'hidden');
        $factura = $builder->getData();
        $estacion = $factura->getEstacion();
        
        $builder->add('impresora', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Impresora',
            'label' => 'Impresora',   
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($estacion)  {
                $query = $er->createQueryBuilder('i')
                            ->where('i.activo=1');
                if($estacion !== null){
                    $query->leftJoin("i.estacion", "e");
                    $query->andWhere("e.id is null or e.id=:idEstacionUsuario");
                    $query->setParameter("idEstacionUsuario", $estacion->getId());
                }
                return $query;
            }
        ));
    }

    public function getName()
    {
        return 'series_factura_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Factura',
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
