<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class AdicionarCorteVentaTalonarioType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        /*****************************************
         *          OTRAS PROPIEDADES
         * ***************************************
         *  'required' => true,
         *  'read_only' => true,
         *  
         */ 
       $entityManager = $options['em'];
       $user = $options['user'];
       
       $builder->add('tarjeta', 'text', array(
           'label' => 'Tarjeta',
           'virtual' => true,
           'attr' =>  array('class' => 'span12')
       ));   
       
       $builder->add('talonario', 'text', array(
           'label' => 'Talonario',
           'virtual' => true,
           'attr' =>  array('class' => 'span12')
       )); 
       
       $builder->add('inicial', 'integer' , array(
            'label' => 'Del',
            'required' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12')
       ));
        
       $builder->add('final', 'integer' , array(
            'label' => 'Al',
            'required' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12')
       ));
       
       $builder->add('importeTotal', 'integer' , array(
            'label' => 'Importe',
            'required' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12')
       ));
       
       
        
        $esInspector = $user->hasRole("ROLE_INSPECTOR_BOLETO");
        if(!$esInspector){
            
            $builder->add('estacionCreacion', 'entity', array(
                 'class' => 'AcmeTerminalOmnibusBundle:Estacion',
                 'label' => 'Punto de Control',
                 'property' => 'info1',
                 'required' => true,
                 'multiple'  => false,
                 'expanded'  => false,
                 'attr' =>  array('class' => 'span12'),
                 'query_builder' => function(EntityRepository $er){
                     $query = $er->createQueryBuilder('u');
                     return $query;
                 }
             ));
        
            $builder->add('fecha', 'date',  array(
                'label' => 'Fecha',
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'data' => new \DateTime(),
                'attr' =>  array('class' => 'span12')
            ));
            
            $builder->add('inspector', 'entity', array(
                'class' => 'AcmeBackendBundle:User',
                'label' => 'Inspector',
                'multiple'  => false,
                'expanded'  => false,
                'empty_value' => "",
                'empty_data' => null,
                'required' => false,
                'attr' =>  array('class' => 'span12'),
                'query_builder' => function(EntityRepository $er) use ($user)  {
                    $query = $er->createQueryBuilder('u');
                    $query->leftJoin("u.empresas", "em");
                    $query->andWhere('em.id IN (:keyEmpresasUsuario)');
                    $query->andWhere("u.roles like '%ROLE_INSPECTOR_BOLETO%'");
                    $query->setParameter("keyEmpresasUsuario", $user->getIdEmpresas());
                    return $query;
                }
            ));
        }else{
            
            $idEstacionesPermitidas = array();
            $estacionUsuario = $user->getEstacion();
            if($estacionUsuario !== null){
                $idEstacionesPermitidas[] =  $estacionUsuario->getId();
            }
            $estacionesPermitidas = $user->getEstacionesPermitidas();
            foreach ($estacionesPermitidas as $item) {
               $idEstacionesPermitidas[] =  $item->getId();
            }
            $builder->add('estacionCreacion', 'entity', array(
                 'class' => 'AcmeTerminalOmnibusBundle:Estacion',
                 'label' => 'Punto de Control',
                 'property' => 'info1',
                 'required' => true,
                 'multiple'  => false,
                 'expanded'  => false,
                 'attr' =>  array('class' => 'span12'),
                 'query_builder' => function(EntityRepository $er) use ($idEstacionesPermitidas)  {
                     $query = $er->createQueryBuilder('u');
                     $query->andWhere('u.id IN (:idEstacionesPermitidas)');
                     $query->setParameter("idEstacionesPermitidas", $idEstacionesPermitidas);
                     return $query;
                 }
             ));
            
        }
        
    }

    public function getName()
    {
        return 'adicionar_corte_venta_talonario_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\CorteVentaTalonario',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
            'em',
        ))->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
