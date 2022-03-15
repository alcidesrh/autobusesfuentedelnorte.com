<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\MonedaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoPagoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\EncomiendaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ClienteToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\FacturaToNumberTransformer;

class EntregarEncomiendaType extends AbstractType{
    
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
       
       $builder->add('idEstacionOrigen', 'hidden', array(
           'virtual' => true,
           'data' => $encomiendaModel->getEncomiendaOriginal()->getEstacionOrigen()->getId()
       ));
       
       $builder->add('idEstacionDestino', 'hidden', array(
           'virtual' => true,
           'data' => $encomiendaModel->getEncomiendaOriginal()->getEstacionDestino()->getId()
       ));
       
       $builder->add('idEmpresa', 'hidden', array(
           'virtual' => true,
           'data' => $encomiendaModel->getEncomiendaOriginal()->getEmpresa()->getId()
       ));
       
       $builder->add('clienteReceptor', 'text',  array(
            'label' => 'CLIENTE: NIT (sin guión) / NOMBRE / DOCUMENTO',
            'required' => true,
            'attr' =>  array('class' => 'span6 autocheck')
        ));
       $builder->get('clienteReceptor')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
       $builder->add('encomiendaOriginal', 'hidden');
       $builder->get('encomiendaOriginal')->addModelTransformer(new EncomiendaToNumberTransformer($entityManager));

       $builder->add('tipoDocumento', 'hidden', array(
           'virtual' => true,
           'data' => $encomiendaModel->getEncomiendaOriginal()->getTipoDocumento()->getId()
       )); 
        
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
                return $er->createQueryBuilder('tp')->where('tp.id=1 and tp.activo=1');
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
        return 'entregar_encomienda_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\EntregarEncomiendaModel',
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
