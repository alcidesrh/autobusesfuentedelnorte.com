<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Boleto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ClienteToNumberTransformer;
use Acme\TerminalOmnibusBundle\Entity\TipoEstacion;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\SalidaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\EstacionToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoDocumentoBoletoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\TipoPagoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\MonedaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\FacturaToNumberTransformer;

class EmitirBoletoType extends AbstractType{

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
        $estacionUsuario = $user->getEstacion();
        $boleto = $builder->getData();
        
        $builder->add('clienteDocumento', 'text',  array(
            'label' => 'CLIENTE: NIT (sin guión) / NOMBRE / DOCUMENTO',
            'required' => true,
            'attr' =>  array('class' => 'span9 autocheck')
        ));
        $builder->get('clienteDocumento')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
        $builder->add('fechaSalida', 'date',  array(
            'label' => 'Fecha de Salida',
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'span12 filterSalida')
        ));
        
        $builder->add('estacionOrigen', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Origen de Salida',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'data' => $estacionUsuario,
            'attr' =>  array('class' => 'span12 filterSalida'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder('e')
                            ->where('e.iniciaRuta=true')
                            ->orderBy('e.nombre');
                return $query;
            }
        ));
        
        $builder->add('salida', 'hidden');
        $builder->get('salida')->addModelTransformer(new SalidaToNumberTransformer($entityManager));
        
        $builder->add('estacionSubeEn', 'text',  array(
            'label' => 'Sube En',
            'required' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('estacionSubeEn')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        $builder->add('estacionBajaEn', 'text',  array(
            'label' => 'Baja En',
            'required' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('estacionBajaEn')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        $builder->add('observacionBajaEn', null, array(
             'label' => 'Observación',
             'required' => false,
             'max_length' => 200,
             'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('utilizarDesdeEstacionOrigenSalida', 'checkbox', array(
             'label' => 'Utilizar desde la estación origen de la salida',
             'required' => false
        ));
        
        $builder->add('listaClienteBoleto', 'hidden');
        
        $builder->add('tipoDocuemento', 'hidden');
        $builder->get('tipoDocuemento')->addModelTransformer(new TipoDocumentoBoletoToNumberTransformer($entityManager));
        
        $builder->add('estacionFacturacionEspecial', 'hidden');
        $builder->get('estacionFacturacionEspecial')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        $builder->add('estacionFacturacionEspecialVirtual', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Solicitante',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'virtual' => true,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder('e')
                            ->where('e.facturacionEspecial=1');
                return $query;
            }
        ));
        $builder->add('serieFacturacionEspecial', 'hidden');
        $builder->get('serieFacturacionEspecial')->addModelTransformer(new FacturaToNumberTransformer($entityManager));       
        $builder->add('serieFacturacionEspecialVirtual', 'text',  array(
            'label' => 'Serie Factura',
            'required' => true,
            'virtual' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('serieFacturacionEspecialVirtual')->addModelTransformer(new FacturaToNumberTransformer($entityManager));
        $builder->add('pingFacturacionEspecial', 'hidden');
        
        //No se utiliza transformer para poder personalizar en el controlador el mensaje de error.
        $builder->add('autorizacionCortesia', 'hidden');
        
        //Se crea un tipo de pago virtual pq el popup extrae el select de formulario base.
        
        $builder->add('tipoPago', 'hidden');
        $builder->get('tipoPago')->addModelTransformer(new TipoPagoToNumberTransformer($entityManager));
        $builder->add('tipoPagoVirtual', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoPago',
            'label' => 'Tipo de Pago',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
//            'empty_value' => "",
//            'empty_data'  => null,
            'virtual' => true,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario) {
                $query = $er->createQueryBuilder('tp')
                            ->andWhere('tp.activo=1');
                if($estacionUsuario !== null && $estacionUsuario->getPermitirTarjeta() === false){
                    $query->andWhere('tp.id=1');
                }
                return $query;
            }
        ));
        
        $builder->add('autorizacionTarjeta', 'hidden');
        $builder->add('autorizacionTarjetaVirtual', 'text',  array(
            'label' => 'Autorización Tarjeta',
            'required' => true,
            'virtual' => true,
            'pattern' => ".*((^\d{0,20}$)).*",
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('totalNeto', 'hidden');
        $builder->add('totalNetoVirtual', 'text',  array(
            'label' => 'Total Neto',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
//            'attr' =>  array('class' => 'span12')
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
//            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('efectivo', 'hidden');
        $builder->add('efectivoVirtual', 'text',  array(
            'label' => 'Efectivo',
            'required' => true,
            'virtual' => true,
            'pattern' => ".*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*"
//            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('vuelto', 'hidden');
        $builder->add('vueltoVirtual', 'text',  array(
            'label' => 'Vuelto',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
//            'attr' =>  array('class' => 'span12')
        ));
        
        
        $builder->add('monedaAgencia', 'hidden');
        $builder->get('monedaAgencia')->addModelTransformer(new MonedaToNumberTransformer($entityManager));
        
        $builder->add('totalNetoAgencia', 'hidden');
        $builder->add('totalNetoAgenciaVirtual', 'text',  array(
            'label' => 'Total Neto',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
        ));
        
        $builder->add('referenciaExternaAgencia', 'hidden');
        $builder->add('referenciaExternaAgenciaVirtual', 'text',  array(
            'label' => 'Referencia Externa',
            'required' => true,
            'virtual' => true,
            'pattern' => ".*((^\d{0,20}$)).*",
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('utilizarBonoAgencia', 'hidden');
        $builder->add('utilizarBonoAgenciaVirtual', 'checkbox',  array(
            'label' => 'Utilizar Bono',
            'virtual' => true
        ));
        
        $builder->add('movil', 'hidden');
        $builder->add('impresorasDisponibles', 'hidden');
        $builder->add('identificadorWeb', 'hidden');
    }

    public function getName()
    {
        return 'emitir_boleto_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\EmitirBoletoModel',
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
