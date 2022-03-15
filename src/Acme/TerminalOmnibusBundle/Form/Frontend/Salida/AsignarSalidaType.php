<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Salida;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class AsignarSalidaType extends AbstractType{

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
       $idEmpresasUser = $user->getIdEmpresas();
       $salida = $builder->getData();
       $empresaSalida = $salida->getEmpresa()->getId(); 
       
       $builder->add('id', 'hidden');
       $builder->add('bus', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Bus',
            'property' => 'busCodigoClasePlaca',
            'label' => 'Bus / Clase / Placa',   
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($empresaSalida, $idEmpresasUser){
                 $query = $er->createQueryBuilder('b')
                             ->addSelect('ABS(b.codigo) AS HIDDEN codigoNumber')
                             ->innerJoin("b.empresa", "e")
                             ->andWhere('b.estado=1')
                             ->andWhere('e.id = :empresaSalida and e.id IN (:idEmpresasUser)')
                             ->setParameter("empresaSalida", $empresaSalida)
                             ->setParameter("idEmpresasUser", $idEmpresasUser)
                             ->orderBy("codigoNumber");
                 return $query;
            }
        ));
        
        $builder->add('piloto', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Piloto',
            'property' => 'codigoFullNameVencimientoLicencia',
            'label' => 'Primer Piloto', 
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'required' => false,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($empresaSalida, $idEmpresasUser){
                 $query = $er->createQueryBuilder('p')
                             ->innerJoin("p.empresa", "e")
                             ->andWhere('p.activo=1')
                             ->andWhere('e.id = :empresaSalida and e.id IN (:idEmpresasUser)')
                             ->setParameter("empresaSalida", $empresaSalida)
                             ->setParameter("idEmpresasUser", $idEmpresasUser)
                             ->orderBy("p.codigo");
                 return $query;
            }
        ));
        
        $builder->add('pilotoAux', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Piloto',
            'property' => 'codigoFullNameVencimientoLicencia',
            'label' => 'Segundo Piloto', 
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'required' => false,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($empresaSalida, $idEmpresasUser){
                 $query = $er->createQueryBuilder('p')
                             ->innerJoin("p.empresa", "e")
                             ->andWhere('p.activo=1')
                             ->andWhere('e.id = :empresaSalida and e.id IN (:idEmpresasUser)')
                             ->setParameter("empresaSalida", $empresaSalida)
                             ->setParameter("idEmpresasUser", $idEmpresasUser)
                             ->orderBy("p.codigo");
                 return $query;
            }
        ));
    }

    public function getName()
    {
        return 'asignar_salida_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Salida',
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
