<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CuadreInspectorType extends AbstractType{

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
        
        $builder->add('fecha', 'date',  array(
            'label' => 'Fecha',
            'required' => true,
            'virtual' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'span8')
        ));
        
        $builder->add('usuario', 'entity', array(
            'class' => 'AcmeBackendBundle:User',
            'label' => 'Inspector',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'empty_value' => "",
            'empty_data' => null,
            'required' => false,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($user)  {
            
                $esInspector = $user->hasRole("ROLE_INSPECTOR_BOLETO");
                $query = $er->createQueryBuilder('u');
                if($esInspector){
                    $query->andWhere('u.id IN (:id)');
                    $query->setParameter("id", $user->getId());
                }else{
                    $query->leftJoin("u.empresas", "em");
                    $query->andWhere('em.id IN (:keyEmpresasUsuario)');
                    $query->andWhere("u.roles like '%ROLE_INSPECTOR_BOLETO%'");
                    $query->setParameter("keyEmpresasUsuario", $user->getIdEmpresas());
                }
                
                return $query;
            }
        ));
    }

    public function getName()
    {
        return 'cuadreInspector';
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
