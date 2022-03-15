<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CuadreVentaBoletoType extends AbstractType{

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
        $builder->add('moneda', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Moneda',
            'label' => 'Moneda', 
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8')
        ));
        
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
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
        //hay que filtrar las rutas que tengan la estacion de origen la misma del operador, si el operador no tiene se listan todas.
        $optionsEstacionUsuario =  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('u');
                if($estacionUsuario !== null){
                    $query->where('u.id=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        );
        if($estacionUsuario === null){
            $optionsEstacionUsuario['empty_value'] = "";
            $optionsEstacionUsuario['empty_data'] = null;
            $optionsEstacionUsuario['required'] = false;
        }else{
            $optionsEstacionUsuario['read_only'] = true;
            $optionsEstacionUsuario['required'] = true;
        }
        $builder->add('estacion', 'entity', $optionsEstacionUsuario);
        
        $keyEmpresasUsuario = $user->getIdEmpresas();
        $builder->add('empresa', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',
            'property' => 'alias',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
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
        return 'cuadreVentaBoleto';
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
