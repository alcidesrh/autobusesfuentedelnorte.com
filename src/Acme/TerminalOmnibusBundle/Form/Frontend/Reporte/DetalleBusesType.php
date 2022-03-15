<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DetalleBusesType extends AbstractType{

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
    }

    public function getName()
    {
        return 'detalleBuses';
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
