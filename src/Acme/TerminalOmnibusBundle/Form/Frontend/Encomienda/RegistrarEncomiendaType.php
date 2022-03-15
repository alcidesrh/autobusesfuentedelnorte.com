<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ClienteToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\EstacionToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoDocumentoEncomiendaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\MonedaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoPagoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\BoletoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoEncomiendaEspecialesToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\FacturaToNumberTransformer;

class RegistrarEncomiendaType extends AbstractType{

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
        $encomienda = $builder->getData();
        
        $builder->add('boleto', 'text',  array(
            'label' => 'BOLETO (IDENTIFICADOR, NOMBRE CLIENTE)',
            'required' => false,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->get('boleto')->addModelTransformer(new BoletoToNumberTransformer($entityManager));
        
        $builder->add('clienteRemitente', 'text',  array(
            'label' => 'CLIENTE REMITENTE: NIT (sin guión) / NOMBRE / DOCUMENTO',
            'required' => true,
            'attr' =>  array('class' => 'span8 autocheck')
        ));
        $builder->get('clienteRemitente')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
        $builder->add('clienteDestinatario', 'text',  array(
            'label' => 'CLIENTE DESTINATARIO: NIT (sin guión) / NOMBRE / DOCUMENTO',
            'required' => true,
            'attr' =>  array('class' => 'span8 autocheck')
        ));
        $builder->get('clienteDestinatario')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
        $estacionUsuario = $user->getEstacion();
        $builder->add('estacionOrigen', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Origen',
            'data' => $estacionUsuario,
            'required' => true,
            'read_only' => true,
            'attr' =>  array('class' => 'span8', 'read_only' => true)
        ));
        
        $builder->add('estacionDestino', 'hidden');
        $builder->get('estacionDestino')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        //Componentes para registrar en un popup la secuencia de rutas y estaciones --- init
        $builder->add('rutaVirtual', 'text',  array(
            'label' => 'Ruta',
            'required' => true,
            'virtual' => true,
            'read_only' => false,
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('estacionFinalVirtual', 'text',  array(
            'label' => 'Estación Final',
            'required' => true,
            'virtual' => true,
            'read_only' => false,
            'attr' =>  array('class' => 'span12')
        ));
        //Componentes para registrar en un popup la secuencia de rutas y estaciones --- end

        $builder->add('listaEncomiendaRutas', 'hidden');
        $builder->add('listaEncomiendas', 'hidden');
        
        $builder->add('tipoDocuemento', 'hidden');
        $builder->get('tipoDocuemento')->addModelTransformer(new TipoDocumentoEncomiendaToNumberTransformer($entityManager));
        
        //No se utiliza transformer para poder personalizar en el controlador el mensaje de error.
        $builder->add('autorizacionCortesia', 'hidden');
        
        //No se utiliza transformer para poder personalizar en el controlador el mensaje de error.
        $builder->add('autorizacionInterna', 'hidden');
        
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
        $builder->add('monedaPagoVirtual', 'text',  array(
            'label' => 'Moneda Pago',
            'required' => true,
            'virtual' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('monedaPagoVirtual')->addModelTransformer(new MonedaToNumberTransformer($entityManager));
        
        $builder->add('serieFactura', 'hidden');
        $builder->get('serieFactura')->addModelTransformer(new FacturaToNumberTransformer($entityManager));       
        $builder->add('serieFacturaVirtual', 'text',  array(
            'label' => 'Serie Factura',
            'required' => true,
            'virtual' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('serieFacturaVirtual')->addModelTransformer(new FacturaToNumberTransformer($entityManager));
        
        $builder->add('referenciaExterna', 'hidden');
        $builder->add('referenciaExternaVirtual', 'text',  array(
            'label' => 'Referencia Externa',
            'required' => true,
            'virtual' => true,
            'pattern' => ".*((^\d{0,20}$)).*",
            'attr' =>  array('class' => 'span12')
        ));
        
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
        
        $builder->add('impresorasDisponibles', 'hidden');
        
        $builder->add('cantidadVirtual', 'text',  array(
            'label' => 'Cantidad',
            'required' => false,
            'virtual' => true,
            'data' => 1,
            'pattern' => ".*(^\d{1,8}$).*",
            'attr' =>  array('class' => 'span12')
        ));
        $builder->add('tipoEncomiendaVirtual', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoEncomienda',
            'label' => 'Tipo',  
            'property' => 'nombre',
            'required' => false,
            'virtual' => true,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span12')
        ));       
        $builder->add('valorDeclaradoVirtual', 'text',  array(
            'label' => 'Valor Declarado',
            'required' => false,
            'virtual' => true,
            'pattern' => ".*(^\d{1,8}$).*",
            'attr' =>  array('class' => 'span12')
        ));
        $builder->add('tipoEncomiendaEspecialVirtual', 'text',  array(
            'label' => 'Encomienda Especial',
            'required' => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('tipoEncomiendaEspecialVirtual')->addModelTransformer(new TipoEncomiendaEspecialesToNumberTransformer($entityManager));
//        $builder->add('tipoEncomiendaEspecialVirtual', 'entity',  array(
//            'class' => 'AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales',
//            'label' => 'Nombre',   
//            'required' => false,
//            'virtual' => true,
//            'multiple'  => false,
//            'expanded'  => false,
//            'attr' =>  array('class' => 'span12')
//        ));
        $builder->add('volumenAltoVirtual', 'text',  array(
            'label' => 'Alto (cm):',
            'required' => false,
            'virtual' => true,
            'data' => 1,
            'pattern' => ".*(^\d{0,5}$).*",
            'attr' =>  array('class' => 'span12')
        ));
        $builder->add('volumenAnchoVirtual', 'text',  array(
            'label' => 'Ancho (cm):',
            'required' => false,
            'virtual' => true,
            'data' => 1,
            'pattern' => ".*(^\d{0,5}$).*",
            'attr' =>  array('class' => 'span12')
        ));
        $builder->add('volumenProfundidadVirtual', 'text',  array(
            'label' => 'Profundidad (cm):',
            'required' => false,
            'virtual' => true,
            'data' => 1,
            'pattern' => ".*(^\d{0,5}$).*",
            'attr' =>  array('class' => 'span12')
        ));
        $builder->add('pesoVirtual', 'text',  array(
            'label' => 'Peso (Lb):',
            'required' => false,
            'virtual' => true,
            'data' => 1,
            'pattern' => ".*(^\d{0,5}$).*",
            'attr' =>  array('class' => 'span12')
        ));        
        $builder->add('descripcionVirtual', 'textarea', array(
             'label' => 'Descripción',
             'required' => false,
             'virtual' => true,
             'attr' =>  array('class' => 'span12')
        ));
        $builder->add('identificadorWeb', 'hidden');
        
        $builder->add('codigoExternoCliente', 'text',  array(
            'label' => 'Códigos Externos de Clientes',
            'required' => false,
            'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'registrar_encomienda_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\RegistrarEncomiendaModel',
 	    'cascade_validation' => true,
            'csrf_protection'   => false
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
