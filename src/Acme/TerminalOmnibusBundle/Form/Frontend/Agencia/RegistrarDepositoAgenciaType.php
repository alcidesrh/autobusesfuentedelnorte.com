<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Agencia;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Entity\TipoEstacion;

class RegistrarDepositoAgenciaType extends AbstractType{

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
        
         $user = $options["user"];
        
         $empresasUsuario = $user->getEmpresas();
         $keyEmpresasUsuario = array();
         foreach ($empresasUsuario as $item) {
            $keyEmpresasUsuario[] = $item->getId();
         }
         
         $estacionUsuario = $user->getEstacion();
         $optionsEstacionUsuario =  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Agencia',
            'property' => 'info1',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span4'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('u');
                $query->leftJoin('u.tipo', 't');
                $query->leftJoin('u.tipoPago', 'tp');
                $query->andWhere('t.id=4 and tp.id=2');
                if($estacionUsuario !== null){
                    $query->andWhere('u.id=:estacionUsuario');
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
         
         $builder->add('fecha', 'date',  array(
            'label' => 'Fecha',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'span12')
         ));
        
         $builder->add('importe', 'text',  array(
            'label' => 'Importe', 
            'required' => true,
            'attr' =>  array('class' => 'span12'),
            'pattern' => '.*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*'
         ));
        
        $builder->add('numeroBoleta', 'text', array(
             'label' => 'Número Boleta',
             'required' => true,
             'max_length' => 15,
             'attr' =>  array('class' => 'span12')
        ));
        
        $isUserAgencia = ($estacionUsuario !== null && $estacionUsuario->getTipo()->getId() === TipoEstacion::AGENCIA);
        $builder->add('aplicaBono', 'checkbox', array(
            'label' => 'Aplica Bono',
            'required' => false,
            'read_only' => $isUserAgencia,
            'disabled' => $isUserAgencia,
            'attr' =>  array('class' => 'checkbox'),
        ));
        
        $builder->add('observacion', 'textarea', array(
             'label' => 'Observación',
             'required' => false,
             'max_length' => 200,
             'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'registrar_deposito_agencia_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\DepositoAgencia',
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
