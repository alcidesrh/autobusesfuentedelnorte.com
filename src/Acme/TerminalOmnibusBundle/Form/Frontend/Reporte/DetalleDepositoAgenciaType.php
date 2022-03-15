<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DetalleDepositoAgenciaType extends AbstractType{

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
        $builder->add('rangoFecha', 'text',  array(
            'label' => 'Rango Fecha',
            'required' => false,
            'read_only' => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8')
        ));
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
        //hay que filtrar las rutas que tengan la estacion de origen la misma del operador, si el operador no tiene se listan todas.
        $optionsEstacionUsuario =  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Agencia',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('e');
                $query->leftJoin("e.tipo", "t");
                $query->andWhere('t.id=4');
                if($estacionUsuario !== null){
                    $query->andWhere('e.id=:estacionUsuario');
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
    }

    public function getName()
    {
        return 'detalleDepositoAgencia';
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
