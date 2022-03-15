<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\MonedaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoPagoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ClienteToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\FacturaToNumberTransformer;

class EntregaMultipleEncomiendaType extends AbstractType{
    
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

       $entityManager = $options['em'];
       $user = $options['user'];
       $encomiendaModel = $builder->getData();
        
       $estacionUsuario = $user->getEstacion();
       $builder->add('estacion', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'read_only' => $estacionUsuario !== null,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('e');
                if($estacionUsuario !== null){
                    $query->andWhere('e.id=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        ));
       
        $idEmpresasUsuario = array();
        $empresasUsuario = $user->getEmpresas();
        foreach ($empresasUsuario as $empresa) {
            $idEmpresasUsuario[] = $empresa->getId();
        }
        $builder->add('empresa', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',   
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($idEmpresasUsuario)  {
                $query = $er->createQueryBuilder('e')->where('e.activo=1');
                $query->andWhere('e.id IN (:idEmpresasUsuario) ');
                $query->setParameter("idEmpresasUsuario", $idEmpresasUsuario);
                return $query;
            }
        ));
       
        $builder->add('importeTotal', 'text', array(
             'label' => 'Importe Total',
             'required' => true,
             'read_only' => true,
             'attr' =>  array('class' => 'span12')
        ));
                
       $builder->add('clienteReceptor', 'text',  array(
            'label' => 'CLIENTE: NIT (sin guión) / NOMBRE / DOCUMENTO',
            'required' => true,
            'attr' =>  array('class' => 'span6 autocheck')
        ));
       $builder->get('clienteReceptor')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
       $builder->add('tipoDocumentoEncomienda', 'hidden');
       $builder->add('listaIdEncomiendas', 'hidden');
       
       //Se crea un tipo de pago virtual pq el popup extrae el select de formulario base.
        $builder->add('tipoPago', 'hidden');
        $builder->get('tipoPago')->addModelTransformer(new TipoPagoToNumberTransformer($entityManager));
        $builder->add('tipoPagoVirtual', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoPago',
            'label' => 'Tipo de Pago',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('tp')
                        ->where('tp.id=1 and tp.activo=1');
            }
        ));
        
        $builder->add('totalNeto', 'hidden');
        $builder->add('totalNetoVirtual', 'text',  array(
            'label' => 'Total Neto',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
        ));
        
        $builder->add('monedaPago', 'hidden');
        $builder->get('monedaPago')->addModelTransformer(new MonedaToNumberTransformer($entityManager));
        $monedas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Caja')->listarMonedasCajasAbiertas($user, true);
        $builder->add('monedaPagoVirtual', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Moneda',
            'choices' => $monedas,
            'label' => 'Moneda Pago',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span6')
        ));
        
        $builder->add('serieFactura', 'hidden');
        $builder->get('serieFactura')->addModelTransformer(new FacturaToNumberTransformer($entityManager));       
        $builder->add('serieFacturaVirtual', 'text',  array(
            'label' => 'Serie Factura',
            'required' => true,
            'virtual' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('serieFacturaVirtual')->addModelTransformer(new FacturaToNumberTransformer($entityManager));
        
        $builder->add('tasa', 'hidden');
        $builder->add('tasaVirtual', 'text',  array(
            'label' => 'Tasa',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('totalPago', 'hidden');
        $builder->add('totalPagoVirtual', 'text',  array(
            'label' => 'Total Neto',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
        ));
        
        $builder->add('efectivo', 'hidden');
        $builder->add('efectivoVirtual', 'text',  array(
            'label' => 'Efectivo',
            'required' => true,
            'virtual' => true,
            'pattern' => ".*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*"
        ));
        
        $builder->add('vuelto', 'hidden');
        $builder->add('vueltoVirtual', 'text',  array(
            'label' => 'Vuelto',
            'required' => true,
            'virtual' => true,
            'read_only' => true
        ));
        
        $builder->add('facturar', 'hidden');
        $builder->add('facturarVirtual', 'checkbox', array(
             'label' => 'Generar Documento',
             'required' => false,
             'virtual' => true,
             'attr' =>  array('class' => 'checkbox'),
        ));
        
        $builder->add('clienteDocumento', 'hidden');
        $builder->get('clienteDocumento')->addModelTransformer(new ClienteToNumberTransformer($entityManager));       
        $builder->add('clienteDocumentoVirtual', 'text',  array(
            'label' => 'Cliente Documento',
            'required' => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span9 autocheck')
        ));
        $builder->get('clienteDocumentoVirtual')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
                
        $builder->add('impresorasDisponibles', 'hidden');
    }

    public function getName()
    {
        return 'entrega_multiple_encomienda_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\EntregaMultipleModel',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
            'em'
        ))
        ->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
