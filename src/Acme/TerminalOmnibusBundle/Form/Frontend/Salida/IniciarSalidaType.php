<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Salida;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\PilotoToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\BusToNumberTransformer;
use Doctrine\ORM\EntityRepository;

class IniciarSalidaType extends AbstractType{
    
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
       $salida = $builder->getData();
       $builder->add('id', 'hidden');
       $totalBoletosChequeados = 0;
       $totalBoletosNOChequeados = 0;
       $totalBoletosPendientes = 0;
       $totalEncomiendasEmbarcadas = 0;
       $idSalida = $salida->getId();
       if( $idSalida !== null && trim($idSalida) !== "" && trim($idSalida) !== "0"){
            $totalBoletosChequeados = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosChequeadosBySalida($idSalida);
            $totalBoletosNOChequeados = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosEmitidosBySalida($idSalida);
            $totalBoletosPendientes = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosEmitidosPendientesBySalida($idSalida);
            $totalEncomiendasEmbarcadas = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->totalEncomiendasEmbarcadasBySalida($idSalida);
       }
       
       $builder->add('totalBoletosChequeados', 'text',  array(
            'label' => 'Total de boletos chequeados', 
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalBoletosChequeados,
            'attr' =>  array('class' => 'span9'),
        ));
       
        $builder->add('totalBoletosNOChequeados', 'text',  array(
            'label' => 'Total de boletos emitidos que aún no se han chequeado', 
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalBoletosNOChequeados,
            'attr' =>  array('class' => 'span9'),
        ));
        
        $builder->add('totalBoletosPendientes', 'text',  array(
            'label' => 'Total de boletos emitidos que no se chequean en la salida', 
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalBoletosPendientes,
            'attr' =>  array('class' => 'span9'),
        ));
       
        $builder->add('totalEncomiendasEmbarcadas', 'text',  array(
            'label' => 'Total de encomiendas embarcadas',
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalEncomiendasEmbarcadas,
            'attr' =>  array('class' => 'span9'),
        ));
        
    }

    public function getName()
    {
        return 'iniciar_salida_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Salida',
 	    'cascade_validation' => true,
    	));
    }
}

?>
