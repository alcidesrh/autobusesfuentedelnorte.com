<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DetalleGeneralEncomiendaType extends AbstractType{

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
        
        $user = $options["user"];
        
        $builder->add('rangoFecha', 'text',  array(
            'label' => 'Rango Fecha',
            'required' => true,
            'read_only' => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8')
        ));
        
        $keyEmpresasUsuario = $user->getIdEmpresas();
        $builder->add('empresa', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',
            'property' => 'alias',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'read_only'  => false,
            'required'  => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($keyEmpresasUsuario)  {
                $query = $er->createQueryBuilder('e');
                $query->andWhere('e.activo=1 and e.id IN (:keyEmpresasUsuario)');
                $query->setParameter("keyEmpresasUsuario", $keyEmpresasUsuario);
                return $query;
            }
        ));
        
        $builder->add('estacionOrigen', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Origen',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er){
                $query = $er->createQueryBuilder('u');
                $query->where('u.destino=1');
                return $query;
            }
        ));
        
        $builder->add('estacionDestino', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Destino',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er){
                $query = $er->createQueryBuilder('u');
                $query->where('u.destino=1');
                return $query;
            }
        ));
        
        $builder->add('mostrarSoloFacturado', 'checkbox', array(
             'label' => 'Mostrar solo facturado',
             'required' => false,
             'virtual' => true,
             'data' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));

        $builder->add('mostrarSoloPorCobrar', 'checkbox', array(
             'label' => 'Mostrar solo por cobrar',
             'required' => false,
             'virtual' => true,
             'data' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
    }

    public function getName()
    {
        return 'detalleGeneralEncomienda';
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
